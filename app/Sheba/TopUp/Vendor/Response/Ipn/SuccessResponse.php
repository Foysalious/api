<?php

namespace Sheba\TopUp\Vendor\Response\Ipn;


use App\Models\TopUpOrder;

abstract class SuccessResponse
{
    /** @var array $response */
    protected $response;

    abstract public function setResponse($response);

    abstract public function getTopUpOrder(): TopUpOrder;

    abstract public function getSuccessfulTransactionDetails();
}