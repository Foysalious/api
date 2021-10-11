<?php

namespace App\Sheba\Pos\Repositories;

use App\Sheba\Pos\PosClient;

class PosClientRepository extends PosClient
{
    private $partnerId;
    private $orderId;

    public function addOnlinePayment(array $data)
    {

    }

    /**
     * @param mixed $partnerId
     * @return PosClientRepository
     */
    public function setPartnerId($partnerId): PosClientRepository
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $orderId
     * @return PosClientRepository
     */
    public function setOrderId($orderId): PosClientRepository
    {
        $this->orderId = $orderId;
        return $this;
    }
}