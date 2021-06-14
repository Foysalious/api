<?php namespace Sheba\Pos\Order;

use App\Exceptions\DoNotReportException;
use App\Exceptions\Pos\Customer\PartnerPosCustomerNotFoundException;
use App\Exceptions\Pos\Customer\PosCustomerNotFoundException;
use App\Exceptions\Pos\Order\NotEnoughStockException;
use App\Models\Partner;
use App\Models\PartnerPosCustomer;
use App\Models\PartnerPosService;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\Profile;
use App\Sheba\AccountingEntry\Constants\EntryTypes;
use App\Sheba\AccountingEntry\Repository\AccountingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Sheba\AccountingEntry\Accounts\Accounts;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Dal\POSOrder\SalesChannels;
use Sheba\ExpenseTracker\AutomaticIncomes;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;
use Sheba\ExpenseTracker\Repository\AutomaticEntryRepository;
use Sheba\Pos\Discount\DiscountTypes;
use Sheba\Pos\Discount\Handler as DiscountHandler;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Pos\Product\StockManager;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Pos\Validators\OrderCreateValidator;
use Sheba\Voucher\DTO\Params\CheckParamsForPosOrder;

class Creator
{
    /** @var array $data */
    private $data;
    /** @var PosOrderRepository $orderRepo */
    private $orderRepo;
    /** @var PosOrderItemRepository $itemRepo */
    private $itemRepo;
    /** @var PaymentCreator $paymentCreator */
    private $paymentCreator;
    /** @var StockManager $stockManager */
    private $stockManager;
    /** @var OrderCreateValidator $createValidator */
    private $createValidator;
    /** @var PosCustomer $customer */
    private $customer;
    /** @var Partner $partner */
    private $partner;
    private $address;
    /** @var DiscountHandler $discountHandler */
    private $discountHandler;
    private $posServiceRepo;
    private $paymentMethod;
    /** @var OrderStatuses $status */
    protected $status;
    /** @var Request */
    private $request;

    public function __construct(
        PosOrderRepository $order_repo,
        PosOrderItemRepository $item_repo,
        PaymentCreator $payment_creator,
        StockManager $stock_manager,
        OrderCreateValidator $create_validator,
        DiscountHandler $discount_handler,
        PosServiceRepositoryInterface $posServiceRepo
    ) {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
        $this->paymentCreator = $payment_creator;
        $this->stockManager = $stock_manager;
        $this->createValidator = $create_validator;
        $this->discountHandler = $discount_handler;
        $this->posServiceRepo  = $posServiceRepo;
    }

    /**
     * @return array
     */
    public function hasError()
    {
        return $this->createValidator->hasError();
    }

    public function hasDueError()
    {
        if ($this->resolveCustomerId() !== null) {
            return false;
        }
        if ($this->data['paid_amount'] - $this->getNetPrice() >= 0) {
            return false;
        }
        return ['code' => 421, 'msg' => 'Can not make due order with out customer'];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->createValidator->setServices(json_decode($this->data['services'], true));
        if (!isset($this->data['payment_method'])) {
            $this->data['payment_method'] = 'cod';
        }
        if (isset($this->data['customer_address'])) {
            $this->setAddress($this->data['customer_address']);
        }
        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->setData($request->all());
        return $this;
    }

    public function setCustomer(PosCustomer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return PosOrder
     * @throws AccountingEntryServerError
     * @throws DoNotReportException
     * @throws InvalidDiscountType
     * @throws NotEnoughStockException
     * @throws PartnerPosCustomerNotFoundException
     * @throws PosCustomerNotFoundException|ExpenseTrackingServerError
     */
    public function create()
    {
        $default_instance = 0;

        $order_data['partner_id']            = $this->partner->id;
        $order_data['customer_id']           = $this->resolveCustomerId();
        $order_data['address']               = $this->address;
        $order_data['previous_order_id']     = (isset($this->data['previous_order_id']) && $this->data['previous_order_id']) ? $this->data['previous_order_id'] : null;
        $order_data['partner_wise_order_id'] = $this->createPartnerWiseOrderId($this->partner);
        $order_data['emi_month']             = isset($this->data['emi_month']) ? $this->data['emi_month'] : null;
        $order_data['sales_channel']         = isset($this->data['sales_channel']) ? $this->data['sales_channel'] : SalesChannels::POS;
        $order_data['delivery_charge']       = isset($this->data['delivery_charge'])   ? $this->data['delivery_charge'] : 0;
        $order_data['status']                = $this->status;
        $order_data['weight']                = isset($this->data['weight'])   ? $this->data['weight'] : 0;
        $order_data['delivery_district']     = isset($this->data['sales_channel']) && $this->data['sales_channel'] == SalesChannels::WEBSTORE && isset($this->data['delivery_district']) ? $this->data['delivery_district'] : null;
        $order_data['delivery_thana']        = isset($this->data['sales_channel']) && $this->data['sales_channel'] == SalesChannels::WEBSTORE && isset($this->data['delivery_thana']) ? $this->data['delivery_thana'] : null;
        $order                               = $this->orderRepo->save($order_data);
        $services                            = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            /** @var PartnerPosService $original_service */
            if(isset($service['id']) && !empty($service['id'])) $original_service = $this->posServiceRepo->find($service['id']);
            else {
                $vat_percentage = $this->partner->posSetting->vat_percentage;
                $original_service = $this->posServiceRepo->defaultInstance($service, $vat_percentage);
            }
            if(!$original_service)
                throw new DoNotReportException("Service not found with provided ID", 400);
            if($original_service->is_published_for_shop && isset($service['quantity']) && !empty($service['quantity']) && $service['quantity'] > $original_service->stock)
                throw new NotEnoughStockException("Not enough stock", 403);

            // $is_service_discount_applied = $original_service->discount();
            $service_wholesale_applicable = $original_service->wholesale_price ? true : false;

            $service['service_id']     = $original_service->id;
            $service['service_name']   = isset($service['name']) ? $service['name'] : $original_service->name;
            $service['pos_order_id']   = $order->id;
            $service['unit_price']     = (isset($service['updated_price']) && $service['updated_price']) ? $service['updated_price'] : ($this->isWholesalePriceApplicable($service_wholesale_applicable) ? $original_service->wholesale_price : $original_service->price);
            $service['warranty']       = $original_service->warranty;
            $service['warranty_unit']  = $original_service->warranty_unit;
            $service['vat_percentage'] = (!isset($service['is_vat_applicable']) || $service['is_vat_applicable']) ? $original_service->vat_percentage : 0.00;
            $service['note']           = isset($service['note']) ? $service['note'] : null;
            $service                   = array_except($service, ['id', 'name', 'is_vat_applicable', 'updated_price']);

            $pos_order_item        = $this->itemRepo->save($service);
            $is_stock_maintainable = $this->stockManager->setPosService($original_service)->isStockMaintainable();
            if ($is_stock_maintainable) $this->stockManager->decrease($service['quantity']);

            $this->discountHandler->setOrder($order)->setPosService($original_service)->setType(DiscountTypes::SERVICE)->setData($service);
            if ($this->discountHandler->hasDiscount()) $this->discountHandler->setPosOrderItem($pos_order_item)->create($order);
        }

        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $order->id;
            $payment_data['amount']       = $this->data['paid_amount'];
            $payment_data['method']       = $this->data['payment_method'] ?: 'cod';
            $this->paymentCreator->credit($payment_data);
        }

        $order = $order->calculate();
        $this->discountHandler->setOrder($order)->setType(DiscountTypes::ORDER)->setData($this->data);
        if ($this->discountHandler->hasDiscount()) $this->discountHandler->create($order);

        $this->voucherCalculation($order);
        $this->resolvePaymentMethod();
        $this->storeIncome($order);
        Log::info(['checking create data', $this->data['services'], $this->request->paid_amount, $this->request->payment_method, $this->request->refund_nature, $this->request->return_nature]);
        $this->storeJournal($order);
        return $order;
    }

    /**
     * @return mixed|null
     * @throws PosCustomerNotFoundException
     * @throws PartnerPosCustomerNotFoundException
     */
    private function resolveCustomerId()
    {
        if ($this->customer) return $this->customer->id;
        if (!isset($this->data['customer_id']) || !$this->data['customer_id']) return null;
        $pos_customer = PosCustomer::find($this->data['customer_id']);
        if (!$pos_customer) throw new PosCustomerNotFoundException("Customer #" . $this->data['customer_id'] . " Doesn't Exists.");
        $partner_pos_customer = PartnerPosCustomer::where('partner_id', $this->partner->id)->where('customer_id', $this->data['customer_id'])->first();
        if (!$partner_pos_customer) throw new PartnerPosCustomerNotFoundException("Customer #" . $this->data['customer_id'] . " Doesn't Belong To Partner #" . $this->partner->id);
        return $this->data['customer_id'];
    }


    private function resolvePaymentMethod()
    {
        if (isset($this->data['payment_method']))
            $this->paymentMethod = $this->data['payment_method'];
        else
            $this->paymentMethod = 'cod';

    }

    private function createPartnerWiseOrderId(Partner $partner)
    {
        $lastOrder    = $partner->posOrders()->orderBy('id', 'desc')->first();
        $lastOrder_id = $lastOrder ? $lastOrder->partner_wise_order_id : 0;
        return $lastOrder_id + 1;
    }

    /**
     * @param $service_wholesale_applicable
     * @return bool
     */
    private function isWholesalePriceApplicable($service_wholesale_applicable)
    {
        return isset($this->data['is_wholesale_applied']) && $this->data['is_wholesale_applied'] && $service_wholesale_applicable;
    }

    /**
     * @param PosOrder $order
     * @throws InvalidDiscountType
     */
    private function voucherCalculation(PosOrder $order)
    {
        if (isset($this->data['voucher_code']) && !empty($this->data['voucher_code'])) {
            $code             = strtoupper($this->data['voucher_code']);
            $customer_id      = $this->resolveCustomerId();
            $pos_customer     = PosCustomer::find($customer_id) ?: new PosCustomer();
            $pos_order_params = (new CheckParamsForPosOrder());
            $pos_services     = $order->items->pluck('service_id')->toArray();
            $pos_order_params->setOrderAmount($order->getTotalBill())->setApplicant($pos_customer)->setPartnerPosService($pos_services);
            $result = voucher($code)->checkForPosOrder($pos_order_params)->reveal();

            $this->discountHandler->setOrder($order)->setType(DiscountTypes::VOUCHER)->setData($result);
            if ($this->discountHandler->hasDiscount()) {
                $this->discountHandler->create($order);
                $this->orderRepo->update($order, ['voucher_id' => $result['id']]);
            }
        }
    }

    /**
     * @param PosOrder $order
     * @throws ExpenseTrackingServerError
     */
    private function storeIncome(PosOrder $order)
    {
        /** @var AutomaticEntryRepository $entry */
        $entry   = app(AutomaticEntryRepository::class);
        $order   = $order->calculate();
        $amount  = (double)$order->getNetBill();
        $profile = $order->customer ? $order->customer->profile : new Profile();
        $entry->setPartner($this->partner)
              ->setParty($profile)
              ->setAmount($amount)
              ->setAmountCleared($order->getPaid())
              ->setHead($order->sales_channel == SalesChannels::POS ? AutomaticIncomes::POS : AutomaticIncomes::WEBSTORE_SALES )
              ->setSourceType(class_basename($order))
              ->setInterest($order->interest)
              ->setSourceId($order->id)
              ->setEmiMonth($order->emi_month)
              ->setInterest($order->interest)
              ->setBankTransactionCharge($order->bank_transaction_charge)
              ->setPaymentMethod($this->paymentMethod)
              ->setIsWebstoreOrder($order->sales_channel == SalesChannels::WEBSTORE ? 1 : 0)
              ->store();
    }

    /**
     * @param $is_service_discount_applied
     * @param $data
     * @param $service_wholesale_applicable
     * @return bool
     */
    private function isServiceDiscountApplicable($is_service_discount_applied, $data, $service_wholesale_applicable)
    {
        return $is_service_discount_applied && (!$data['is_wholesale_applied'] || ($data['is_wholesale_applied'] && !$service_wholesale_applicable));
    }

    private function getNetPrice()
    {
        $total_price             = 0;
        $service_discount_amount = 0;
        $voucher_discount_amount = 0;
        $order_discount_amount   = 0;
        $net_price               = 0;
        $service_id              = array();
        $services                = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            /** @var PartnerPosService $original_service */
            $original_service = isset($service['id']) && !empty($service['id']) ? $this->posServiceRepo->find($service['id']) : $this->posServiceRepo->defaultInstance($service);
            if (is_null($original_service)) $original_service = $this->posServiceRepo->defaultInstance($service);
            $service_id[]                 = isset($service['id']) && !empty($service['id']) ? $service['id'] : 0;
            $service_wholesale_applicable = $original_service->wholesale_price ? true : false;
            $service['unit_price']        = (isset($service['updated_price']) && $service['updated_price']) ? $service['updated_price'] : ($this->isWholesalePriceApplicable($service_wholesale_applicable) ? $original_service->wholesale_price : $original_service->price);
            $total_price                  += ($service['unit_price'] * $service['quantity']);

            $this->discountHandler->setPosService($original_service)->setType(DiscountTypes::SERVICE)->setData($service);
            if ($this->discountHandler->hasDiscount()) $service_discount = $this->discountHandler->getBeforeData();
            if (isset($service_discount)) $service_discount_amount += $this->getDiscountAmount($service_discount);
        }
        $this->discountHandler->setType(DiscountTypes::ORDER)->setData($this->data);
        if ($this->discountHandler->hasDiscount()) $order_discount = $this->discountHandler->setOrderAmount($total_price - $service_discount_amount)->getBeforeData();
        if (isset($order_discount)) $order_discount_amount = $this->getDiscountAmount($order_discount);

        if (isset($this->data['voucher_code']) && !empty($this->data['voucher_code'])) {
            $code             = strtoupper($this->data['voucher_code']);
            $customer_id      = $this->resolveCustomerId();
            $pos_customer     = PosCustomer::find($customer_id) ?: new PosCustomer();
            $pos_order_params = (new CheckParamsForPosOrder());
            $pos_services     = $service_id;
            $pos_order_params->setOrderAmount($total_price - $service_discount_amount)->setApplicant($pos_customer)->setPartnerPosService($pos_services);
            $result = voucher($code)->checkForPosOrder($pos_order_params)->reveal();
            $this->discountHandler->setType(DiscountTypes::VOUCHER)->setData($result);
            if ($this->discountHandler->hasDiscount()) $voucher_discount = $this->discountHandler->getBeforeData();
            if (isset($voucher_discount)) $voucher_discount_amount = $this->getDiscountAmount($voucher_discount);
        }
        $net_price = ($total_price - $order_discount_amount - $voucher_discount_amount - $service_discount_amount);
        return round($net_price,2);
    }

    private function getDiscountAmount($discount)
    {
        return (isset($discount) && isset($discount['amount'])) ? $discount['amount'] : 0;
    }

    /**
     * @param PosOrder $order
     * @throws AccountingEntryServerError
     */
    private function storeJournal(PosOrder $order)
    {
        $this->additionalAccountingData($order);
        /** @var AccountingRepository $accounting_repo */
        $accounting_repo = app()->make(AccountingRepository::class);
        $this->request->merge([
            "inventory_products" => $accounting_repo->getInventoryProducts($order->items, $this->data['services']),
        ]);
        if (isset($this->customer->id)) {
            $this->request["customer_id"] = $this->customer->id;
        }
        $accounting_repo->storeEntry($this->request, EntryTypes::POS);
    }

    private function additionalAccountingData(PosOrder $order)
    {
        $order_discount = $order->discounts->count() > 0 ? $order->discounts()->sum('amount') : 0;
        $this->request->merge([
            "from_account_key"   => (new Accounts())->income->sales::SALES_FROM_POS,
            "to_account_key"     => $order->sales_channel == SalesChannels::WEBSTORE ? (new Accounts())->asset->sheba::SHEBA_ACCOUNT : (new Accounts())->asset->cash::CASH,
            "amount"             => (double)$order->getNetBill(),
            "amount_cleared"     => $order->getPaid(),
            "total_discount"     => $order_discount,
            "note"               => $order->sales_channel == SalesChannels::WEBSTORE ? SalesChannels::WEBSTORE : SalesChannels::POS,
            "source_id"          => $order->id,
            "total_vat"          => $order->getTotalVat()
        ]);
    }
}