<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses;


class CreateResponse
{
    private $statusCode;
    private $statusMessage;
    private $paymentID;
    private $bkashURL;
    private $agreementCreateTime;
    private $agreementStatus;
    private $successCallbackURL;
    private $failureCallbackURL;
    private $cancelledCallbackURL;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setResponse($response)
    {
        $this->statusCode = $response->statusCode;
        $this->statusMessage = $response->statusMessage;
        $this->paymentID = $response->paymentID;
        $this->bkashURL = $response->bkashURL;
        $this->agreementCreateTime = $response->agreementCreateTime;
        $this->agreementStatus = $response->agreementStatus;
        $this->successCallbackURL = $response->successCallbackURL;
        $this->failureCallbackURL = $response->failureCallbackURL;
        $this->cancelledCallbackURL = $response->cancelledCallbackURL;
        return $this;
    }
}