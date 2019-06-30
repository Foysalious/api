<?php namespace Sheba\Logistics\Repository;

class OrderRepository extends BaseRepository
{
    public function find($id)
    {
        $result = $this->client->get("orders/$id");
        return !empty($result) ? $result['data'] : $result;
    }

    public function store($data)
    {
        $result = $this->client->post('orders', $data);
        return !empty($result) ? $result['order'] : $result;
    }

    public function retryRiderSearch($order_id)
    {
        $this->client->get("orders/$order_id/retry-search");
    }
}