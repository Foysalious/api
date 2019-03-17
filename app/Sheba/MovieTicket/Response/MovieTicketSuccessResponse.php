<?php namespace Sheba\MovieTicket\Response;

class MovieTicketSuccessResponse
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