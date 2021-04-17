<?php namespace App\Sheba\PosOrderService\Services;

use App\Sheba\PosOrderService\PosOrderServerClient;

class OrderService
{
    /**
     * @var PosOrderServerClient
     */
    private $client;
    private $partnerId;
    private $customerId;
    private $deliveryAddress;
    private $salesChannelId;
    private $deliveryCharge;
    private $status;

    public function __construct(PosOrderServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    public function setSalesChannelId($salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    public function setDeliveryCharge($deliveryCharge)
    {
        $this->deliveryCharge = $deliveryCharge;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function store()
    {
        $data = $this->makeCreateData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/orders', $data, true);
    }

    private function makeCreateData()
    {
       return [
            ['name' => 'partner_id', 'contents' => $this->partnerId],
            ['name' => 'customer_id', 'contents' => $this->customerId],
            ['name' => 'delivery_address','contents' => $this->deliveryAddress],
            ['name' => 'delivery_charge','contents' => $this->deliveryCharge],
            ['name' => 'sales_channel_id','contents' => $this->salesChannelId ?: 0],
            ['name' => 'status','contents' => $this->status ?: 'completed']
        ];
    }


}