<?php namespace Sheba\TopUp\Vendor\Response;

class SslResponse extends TopUpResponse
{
    protected $response;

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function hasSuccess(): bool
    {
        return $this->response->recharge_status == 200;
    }

    public function getSuccess(): TopUpSuccessResponse
    {
        $topup_response = new TopUpSuccessResponse();
        $topup_response->transactionId = $this->response->guid;
        $topup_response->transactionDetails = $this->response;
        return $topup_response;
    }

    public function getError(): TopUpErrorResponse
    {
        if ($this->hasSuccess()) throwException(new \Exception('Response has success'));
        $topup_error = new TopUpErrorResponse();
        $topup_error->errorCode = $this->response->recharge_status;
        $topup_error->errorMessage = $this->response->Message;
        return $topup_error;
    }
}