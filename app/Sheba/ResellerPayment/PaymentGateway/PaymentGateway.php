<?php

namespace App\Sheba\ResellerPayment\PaymentGateway;

class PaymentGateway
{
    private $key;

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getDetails()
    {
        return [
          'banner' =>''
        ];

    }

}