<?php

namespace Sheba\TopUp\Vendor\Response;


class TopUpErrorResponse
{
    private $errorCode;
    private $errorMessage;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}