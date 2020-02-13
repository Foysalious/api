<?php namespace Sheba\Pos\Order;

use App\Models\Partner;
use App\Models\PartnerPosService;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Models\Profile;
use Sheba\Dal\Discount\InvalidDiscountType;
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

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo,
                                PaymentCreator $payment_creator, StockManager $stock_manager,
                                OrderCreateValidator $create_validator, DiscountHandler $discount_handler, PosServiceRepositoryInterface $posServiceRepo)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
        $this->paymentCreator = $payment_creator;
        $this->stockManager = $stock_manager;
        $this->createValidator = $create_validator;
        $this->discountHandler = $discount_handler;
        $this->posServiceRepo = $posServiceRepo;
    }

    /**
     * @return array
     */
    public function hasError()
    {
        return $this->createValidator->hasError();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->createValidator->setServices(json_decode($this->data['services'], true));

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

    /**
     * @return PosOrder
     * @throws InvalidDiscountType
     * @throws ExpenseTrackingServerError
     */
    public function create()
    {
        $order_data['partner_id'] = $this->partner->id;
        $order_data['customer_id'] = $this->resolveCustomerId();
        $order_data['address'] = $this->address;
        $order_data['previous_order_id'] = (isset($this->data['previous_order_id']) && $this->data['previous_order_id']) ? $this->data['previous_order_id'] : null;
        $order_data['partner_wise_order_id'] = $this->createPartnerWiseOrderId($this->partner);
        $order = $this->orderRepo->save($order_data);
        $services = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            /** @var PartnerPosService $original_service */
            $original_service = isset($service['id']) &&!empty($service['id']) ? $this->posServiceRepo->find($service['id']) : $this->posServiceRepo->defaultInstance($service);

            // $is_service_discount_applied = $original_service->discount();
            $service_wholesale_applicable = $original_service->wholesale_price ? true : false;

            $service['service_id'] = $original_service->id;
            $service['service_name'] = isset($service['name']) ? $service['name'] : $original_service->name;
            $service['pos_order_id'] = $order->id;
            $service['unit_price'] = (isset($service['updated_price']) && $service['updated_price']) ? $service['updated_price'] : ($this->isWholesalePriceApplicable($service_wholesale_applicable) ? $original_service->wholesale_price : $original_service->price);
            $service['warranty'] = $original_service->warranty;
            $service['warranty_unit'] = $original_service->warranty_unit;
            $service['vat_percentage'] = (!isset($service['is_vat_applicable']) || $service['is_vat_applicable']) ? $original_service->vat_percentage : 0.00;
            $service['note'] = isset($service['note']) ? $service['note'] : null;
            $service = array_except($service, ['id', 'name', 'is_vat_applicable', 'updated_price']);

            $pos_order_item = $this->itemRepo->save($service);
            $is_stock_maintainable = $this->stockManager->setPosService($original_service)->isStockMaintainable();
            if ($is_stock_maintainable) $this->stockManager->decrease($service['quantity']);

            $this->discountHandler->setOrder($order)->setPosService($original_service)->setType(DiscountTypes::SERVICE)->setData($service);
            if ($this->discountHandler->hasDiscount()) $this->discountHandler->setPosOrderItem($pos_order_item)->create($order);
        }

        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            $payment_data['method'] = $this->data['payment_method'];
            $this->paymentCreator->credit($payment_data);
        }

        $order = $order->calculate();
        $this->discountHandler->setOrder($order)->setType(DiscountTypes::ORDER)->setData($this->data);
        if ($this->discountHandler->hasDiscount()) $this->discountHandler->create($order);

        $this->voucherCalculation($order);

        $this->storeIncome($order);
        return $order;
    }

    /**
     * @return mixed|null
     */
    private function resolveCustomerId()
    {
        if ($this->customer) return $this->customer->id;
        else return (isset($this->data['customer_id']) && $this->data['customer_id']) ? $this->data['customer_id'] : null;
    }

    private function createPartnerWiseOrderId(Partner $partner)
    {
        $lastOrder = $partner->posOrders()->orderBy('id', 'desc')->first();
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
            $code = strtoupper($this->data['voucher_code']);
            $customer_id = $this->resolveCustomerId();
            $pos_customer = PosCustomer::find($customer_id) ?: new PosCustomer();
            $pos_order_params = (new CheckParamsForPosOrder());
            $pos_services = $order->items->pluck('service_id')->toArray();
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
        $entry = app(AutomaticEntryRepository::class);
        $order = $order->calculate();
        $amount = (double)$order->getNetBill();
        $profile = $order->customer ? $order->customer->profile : new Profile();
        $entry->setPartner($this->partner)
            ->setParty($profile)
            ->setAmount($amount)
            ->setAmountCleared($order->getPaid())
            ->setHead(AutomaticIncomes::POS)
            ->setSourceType(class_basename($order))
            ->setSourceId($order->id)
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
}
