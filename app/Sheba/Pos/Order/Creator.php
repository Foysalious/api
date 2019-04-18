<?php namespace Sheba\Pos\Order;

use Sheba\Pos\Repositories\PosOrderItemRepository;
use Sheba\Pos\Repositories\PosOrderRepository;

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

    public function __construct(PosOrderRepository $order_repo, PosOrderItemRepository $item_repo)
    {
        $this->orderRepo = $order_repo;
        $this->itemRepo = $item_repo;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function create()
    {
        $is_discount_applied = (isset($this->data['discount']) && $this->data['discount'] > 0);

        $order_data['customer_id'] = $this->data['customer_id'];
        $order_data['discount'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] * $this->data['amount'] : $this->data['discount']) : 0;
        $order_data['discount_percentage'] = $is_discount_applied ? ($this->data['is_percentage'] ? $this->data['discount'] : 0) : 0;

        $order = $this->orderRepo->save($order_data);
        $services = json_decode($this->data['services'], true);
        foreach ($services as $service) {
            $service['service_id']   = $service['id'];
            $service['service_name'] = $service['name'];
            $service['pos_order_id'] = $order->id;
            $service = array_except($service, ['id', 'name']);

            $this->itemRepo->save($service);
        }

        return $order;
    }
}