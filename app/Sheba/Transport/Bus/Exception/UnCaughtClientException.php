<?php namespace Sheba\Transport\Bus\Exception;

use ReflectionClass;
use Sheba\Transport\Exception\TransportException;
use Throwable;

class UnCaughtClientException extends TransportException
{
    const SEAT_BEING_PROCESSED_BY_OTHERS = "SEAT BEING BOOKED BY OTHERS USER";
    const NOT_ENOUGH_FUNDS = "NOT ENOUGH FUNDS";

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $this->getConstants()[$message];

        parent::__construct($message, $code, $previous);
    }

    private function getConstants()
    {
        return (new ReflectionClass(__CLASS__))->getConstants();
    }
}