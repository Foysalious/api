<?php namespace Sheba\Transport\Bus\Response;

abstract class BusTicketResponse
{
    protected $response;

    abstract public function setResponse($response);

    abstract public function getResponse();

    abstract public function hasSuccess(): bool;

    abstract public function getSuccess(): BusTicketSuccessResponse;

    abstract public function getError(): BusTicketErrorResponse;
}