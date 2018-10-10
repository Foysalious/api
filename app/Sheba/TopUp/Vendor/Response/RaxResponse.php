<?php

namespace Sheba\TopUp\Vendor\Response;

use Sheba\TopUp\TopUpErrorResponse;
use Sheba\TopUp\TopUpSuccessResponse;

class RaxResponse extends TopUpResponse
{
    private $response;

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function hasSuccess(): bool
    {
        return $this->response->TXNSTATUS == 200;
    }

    public function getSuccess(): TopUpSuccessResponse
    {
        $topup_response = new TopUpSuccessResponse();
        $topup_response->transactionId = $this->response->TXNID;
        $topup_response->transactionDetails = json_encode($this->response);
        return $topup_response;
    }

    public function getError(): TopUpErrorResponse
    {
        if ($this->hasSuccess()) throwException(new \Exception('Response has success'));
        $topup_error = new TopUpErrorResponse();
        $topup_error->errorCode = $this->response->TXNID;
        $topup_error->errorMessage = $this->response->MESSAGE;
        return $topup_error;
    }


}