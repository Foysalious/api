<?php

namespace Sheba\PayCharge\Methods;


class MethodError
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