<?php namespace Sheba\Bkash\Modules\Normal\Methods\Payout\Responses;


class IntraAccountTransferResponse
{
    private $response;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function hasSuccess()
    {
        return isset($this->response->transactionStatus) ? $this->response->transactionStatus == 'Completed' : false;
    }
    
}