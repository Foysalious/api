<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses;


class CreateResponse
{
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function isSuccess(): bool
    {
        return $this->response->statusCode && $this->response->statusCode == "0000";
    }

    public function getTransactionId()
    {
        return $this->response->paymentID;
    }

    public function getRedirectUrl()
    {
        return $this->response->bkashURL;
    }
}