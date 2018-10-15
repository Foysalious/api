<?php namespace Sheba\Payment;

class PayChargable extends PayCharged
{
    private $id;
    private $type;
    private $userId;
    private $userType;
    private $redirectUrl;
    private $amount;
    private $serviceName;
    private $completionClass;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}