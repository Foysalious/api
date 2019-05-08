<?php namespace Sheba\Transport\Bus\Response;

class BusTicketErrorResponse
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