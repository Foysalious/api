<?php

namespace Sheba\TopUp\Vendor\Response;


use App\Models\TopUpOrder;
use Illuminate\Http\Request;

abstract class TopUpFailResponse
{
    /** @var array $response */
    protected $response;

    abstract public function setResponse($response);

    abstract public function getTopUpOrder(): TopUpOrder;

    abstract public function getFailedTransactionDetails();
}