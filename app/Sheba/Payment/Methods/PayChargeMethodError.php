<?php

namespace Sheba\Payment\Methods;


class PayChargeMethodError
{

    private $code;
    private $message;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}