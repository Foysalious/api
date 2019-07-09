<?php namespace Sheba\Logistics\Repository;

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
     * @param $data
     * @return mixed
     * @throws LogisticServerError
     */
    public function store($data)
    {
        $result = $this->client->post('orders', $data);
        return !empty($result) ? $result['order'] : $result;
    }
}