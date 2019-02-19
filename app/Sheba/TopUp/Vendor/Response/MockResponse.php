<?php namespace Sheba\TopUp\Vendor\Response;

class MockResponse extends TopUpResponse
{
    protected $response;

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function hasSuccess(): bool
    {
        return $this->response->TXNSTATUS == 200;
    }

    public function getSuccess(): TopUpSuccessResponse
    {
        $topup_response = new TopUpSuccessResponse();
        $topup_response->transactionId = $this->response->TXNID;
        $topup_response->transactionDetails = $this->response;
        return $topup_response;
    }

    public function getError(): TopUpErrorResponse
    {
        if ($this->hasSuccess()) throwException(new \Exception('Response has success'));
        $topup_error = new TopUpErrorResponse();
        $topup_error->errorCode = $this->response->TXNID;
        $topup_error->errorMessage = isset($this->response->MESSAGE) ? $this->response->MESSAGE : 'Error message not given.';
        return $topup_error;
    }


}