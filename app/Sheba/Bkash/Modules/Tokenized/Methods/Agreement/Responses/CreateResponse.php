<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses;


class CreateResponse
{
    private $response;

    public function setResponse($response): CreateResponse
    {
        $this->response = $response;
        return $this;
    }

    public function isSuccess(){

    }
}