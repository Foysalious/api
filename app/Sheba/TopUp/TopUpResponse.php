<?php

namespace Sheba\TopUp;


class TopUpResponse
{
    private $transactionId;
    private $transactionDetails;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}