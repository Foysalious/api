<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses;


class ExecuteResponse
{
    private $agreementID;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setResponse($response)
    {
        $this->agreementID = $response->agreementID;
        return $this;
    }
}