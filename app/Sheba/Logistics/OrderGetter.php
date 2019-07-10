<?php namespace Sheba\Logistics;

use Sheba\Logistics\DTO\Order;

class OrderGetter extends OrderHandler
{
    /**
     * @param $order_id
     * @return Order
     * @throws Exceptions\LogisticServerError
     */
    public function get($order_id)
    {
        $data = $this->repo->find($order_id);
        $order = new Order();
        $order->setStatus($data['status'])->setRider($data['rider'])->setId($data['id']);
        return $order;
    }
}