<?php namespace Sheba\TopUp\Vendor\Response;


class TopUpSuccessResponse
{
    private $transactionId;
    private $transactionDetails;
    private $topUpStatus;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}