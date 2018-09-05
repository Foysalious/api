<?php namespace Sheba\PayCharge;

class PayChargable
{
    private $id;
    private $type;
    private $amount;
    private $serviceName;
    private $completionClass;
    private $userId;
    private $userType;
    private $redirectUrl;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}