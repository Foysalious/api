<?php

namespace Sheba\TopUp\Vendor\Response;


use Sheba\TopUp\TopUpErrorResponse;
use Sheba\TopUp\TopUpSuccessResponse;

abstract class TopUpResponse
{
    protected $response;

    abstract public function setResponse($response);

    abstract public function hasSuccess(): bool;

    abstract public function getSuccess(): TopUpSuccessResponse;

    abstract public function getError(): TopUpErrorResponse;

}