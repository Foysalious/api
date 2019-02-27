<?php namespace Sheba\MovieTicket\Response;


abstract class MovieResponse
{
    protected $response;

    abstract public function setResponse($response);

    abstract public function hasSuccess(): bool;

    abstract public function getSuccess(): MovieTicketSuccessResponse;

    abstract public function getError(): MovieTicketErrorResponse;

}