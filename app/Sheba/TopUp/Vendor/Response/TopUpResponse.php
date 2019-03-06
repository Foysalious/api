<?php namespace Sheba\TopUp\Vendor\Response;

abstract class TopUpResponse
{
    protected $response;

    abstract public function setResponse($response);

    abstract public function hasSuccess(): bool;

    abstract public function getSuccess(): TopUpSuccessResponse;

    abstract public function getError(): TopUpErrorResponse;
}