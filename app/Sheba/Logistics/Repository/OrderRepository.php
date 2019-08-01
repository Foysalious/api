<?php namespace Sheba\Logistics\Repository;

use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\Exceptions\LogisticServerError;

class OrderRepository extends BaseRepository
{
    /**
     * @param $id
     * @return mixed
     * @throws LogisticServerError
     */
    public function find($id)
    {
        $result = $this->client->get("orders/$id");
        return !empty($result) ? $result['data'] : $result;
    }

    /**
     * @param $id
     * @return mixed
     * @throws LogisticServerError
     */
    public function findMinimal($id)
    {
        $result = $this->client->get("orders/$id/minimal");
        return !empty($result) ? $result['data'] : $result;
    }

    /**
     * @param $data
     * @return mixed
     * @throws LogisticServerError
     */
    public function store($data)
    {
        $result = $this->client->post('orders', $data);
        return !empty($result) ? $result['order'] : $result;
    }

    /**
     * @param Order $order
     * @param $data
     * @return mixed
     * @throws LogisticServerError
     */
    public function update(Order $order, $data)
    {
        $result = $this->client->put("orders/$order->id", $data);
        return !empty($result) ? true : false;
    }

    /**
     * @param Order $order
     * @param $date
     * @param $time
     * @return mixed
     * @throws LogisticServerError
     */
    public function reschedule(Order $order, $date, $time)
    {
        $result = $this->client->post("orders/$order->id/reschedule", [
            'date' => $date,
            'time' => $time
        ]);
        return !empty($result) ? true : false;
    }
}