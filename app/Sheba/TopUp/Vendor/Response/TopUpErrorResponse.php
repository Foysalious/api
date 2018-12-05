<?php

namespace Sheba\TopUp\Vendor\Response;


class TopUpErrorResponse
{
    protected $errorCode;
    protected $errorMessage;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}