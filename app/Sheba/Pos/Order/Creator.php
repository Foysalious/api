<?php namespace Sheba\Pos\Order;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Models\PartnerPosService;
use App\Models\PosOrder;
use Sheba\Pos\Product\StockManager;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Pos\Payment\Creator as PaymentCreator;
use Sheba\Pos\Validators\OrderCreateValidator;

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

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo,
                                PaymentCreator $payment_creator, StockManager $stock_manager,
                                OrderCreateValidator $create_validator)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
        $this->paymentCreator = $payment_creator;
        $this->stockManager = $stock_manager;
        $this->createValidator = $create_validator;
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
     */
    public function create()
    {
        $order_data['partner_id'] = $this->partner->id;
        $order_data['customer_id'] = $this->resolveCustomerId();
        $order_data['address'] = $this->address;
        $order_data['previous_order_id'] = (isset($this->data['previous_order_id']) && $this->data['previous_order_id']) ? $this->data['previous_order_id'] : null;
        $order = $this->orderRepo->save($order_data);
        $services = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            $original_service = PartnerPosService::find($service['id']);
            $is_service_discount_applied = $original_service->discount();

            $service['service_id'] = $service['id'];
            $service['service_name'] = $service['name'];
            $service['pos_order_id'] = $order->id;
            $service['unit_price'] = $original_service->price;

            if ($is_service_discount_applied) {
                $service['discount_id'] = $original_service->discount()->id;
                $service['discount'] = $original_service->getDiscount() * $service['quantity'];
                $service['discount_percentage'] = $original_service->discount()->is_amount_percentage ? $original_service->discount()->amount : 0.0;
            }

            $service['vat_percentage'] = (!isset($service['is_vat_applicable']) || $service['is_vat_applicable']) ? PartnerPosService::find($service['id'])->vat_percentage : 0.00;
            $service = array_except($service, ['id', 'name', 'is_vat_applicable']);

            $this->itemRepo->save($service);
            $is_stock_maintainable = $this->stockManager->setPosService($original_service)->isStockMaintainable();
            if ($is_stock_maintainable) $this->stockManager->decrease($service['quantity']);
        }

        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            $payment_data['method'] = $this->data['payment_method'];
            $this->paymentCreator->credit($payment_data);
        }

        $order = $order->calculate();
        $is_discount_applied = (isset($this->data['discount']) && $this->data['discount'] > 0);
        $order_discount_data['discount'] = $is_discount_applied ? ($this->data['is_percentage'] ? (($this->data['discount'] / 100) * $order->getTotalBill()) : $this->data['discount']) : 0;
        $order_discount_data['discount_percentage'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] : 0) : 0;
        $this->orderRepo->update($order, $order_discount_data);

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
}