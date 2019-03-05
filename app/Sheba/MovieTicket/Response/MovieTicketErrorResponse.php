<?php namespace Sheba\MovieTicket\Response;


class MovieTicketErrorResponse
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