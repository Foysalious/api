<?php

namespace Sheba\PayCharge;


class PayCharged
{
    private $id;
    private $type;
    private $user;
    private $userId;
    private $userType;
    private $transactionId;
    private $amount;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}