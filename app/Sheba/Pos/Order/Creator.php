<?php namespace Sheba\Pos\Order;

use App\Models\PartnerPosService;
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
        $order_data['customer_id'] = $this->data['customer_id'];
        $order_data['discount'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] * $this->data['amount'] : $this->data['discount']) : 0;
        $order_data['discount_percentage'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] : 0) : 0;

        $order = $this->orderRepo->save($order_data);
        $services = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            $service['service_id']   = $service['id'];
            $service['service_name'] = $service['name'];
            $service['pos_order_id'] = $order->id;
            $service['vat_percentage'] = PartnerPosService::find($service['id'])->vat_percentage;
            $service = array_except($service, ['id', 'name']);

            $this->itemRepo->save($service);
        }

        if (isset($this->data['paid_amount']) && $this->data['paid_amount'] > 0) {
            $payment_data['pos_order_id'] = $order->id;
            $payment_data['amount'] = $this->data['paid_amount'];
            $payment_data['method'] = $this->data['payment_method'];
            $this->paymentCreator->create($payment_data);
        }

        return $order;
    }
}