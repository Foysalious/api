<?php namespace Sheba\TopUp\Vendor\Response;

abstract class TopUpResponse
{
    protected $response;

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    abstract public function hasSuccess(): bool;

    /**
     * @return mixed
     */
    abstract public function getTransactionId();

    /**
     * @return mixed
     */
    abstract public function getErrorCode();

    /**
     * @return string
     */
    abstract public function getErrorMessage();


    /**
     * @return TopUpSuccessResponse
     * @throws \Exception
     */
    public function getSuccess(): TopUpSuccessResponse
    {
        if (!$this->hasSuccess()) throw new \Exception('Response does not have success.');

        $topup_response = new TopUpSuccessResponse();
        $topup_response->transactionId = $this->getTransactionId();
        $topup_response->transactionDetails = $this->response;
        return $topup_response;
    }

    /**
     * @return TopUpErrorResponse
     * @throws \Exception
     */
    public function getError(): TopUpErrorResponse
    {
        if ($this->hasSuccess()) throw new \Exception('Response has success.');

        $topup_error = new TopUpErrorResponse();
        $topup_error->errorCode = $this->response->recharge_status;
        $topup_error->errorMessage = $this->response->Message;
        return $topup_error;
    }

}