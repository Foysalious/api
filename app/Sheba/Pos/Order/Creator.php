<?php namespace Sheba\Pos\Order;

use App\Models\PartnerPosService;
use App\Models\Service;
use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Pos\Payment\Creator as PaymentCreator;

class Creator
{
    /**
     * @var array
     */
    private $data;
    /**
     * @var PosOrderRepository
     */
    private $orderRepo;
    /**
     * @var PosOrderItemRepository
     */
    private $itemRepo;
    /**
     * @var PaymentCreator
     */
    private $paymentCreator;

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo, PaymentCreator $payment_creator)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
        $this->paymentCreator = $payment_creator;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function create()
    {
        $is_discount_applied = (isset($this->data['discount']) && $this->data['discount'] > 0);

        $order_data['partner_id'] = $this->data['partner']['id'];
        $order_data['discount'] = $is_discount_applied ? ($this->data['is_percentage'] ? (($this->data['discount'] / 100) * $this->data['amount']) : $this->data['discount']) : 0;
        $order_data['discount_percentage'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] : 0) : 0;
        if (isset($this->data['customer_id']) && $this->data['customer_id']) {
            $order_data['customer_id'] = $this->data['customer_id'];
        }
        $order = $this->orderRepo->save($order_data);

        $services = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            $original_service = PartnerPosService::find($service['id']);
            $is_service_discount_applied = $original_service->discount();

            $service['service_id']   = $service['id'];
            $service['service_name'] = $service['name'];
            $service['pos_order_id'] = $order->id;
            $service['unit_price'] = $original_service->price;

            if ($is_service_discount_applied) {
                $service['discount_id'] = $original_service->discount()->id;
                $service['discount'] = $original_service->getDiscount();
                $service['discount_percentage'] = $original_service->discount()->is_amount_percentage ? $original_service->discount()->amount : 0.0;
            }

            $service['vat_percentage'] = PartnerPosService::find($service['id'])->vat_percentage;
            $service = array_except($service, ['id', 'name']);

            $this->itemRepo->save($service);
        }

        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            $payment_data['method'] = $this->data['payment_method'];
            $payment_data['transaction_type'] = 'Credit';
            $this->paymentCreator->create($payment_data);
        }

        return $order;
    }
}