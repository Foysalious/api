<?php

namespace Sheba\Bkash\Modules\Normal\Methods\Payout\Responses;


class B2CPaymentResponse
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

    public function getSuccess()
    {
        return array(
            'completed_time' => $this->response->completedTime,
            'trxID' => $this->response->trxID,
            'status' => $this->response->transactionStatus,
            'amount' => (double)$this->response->amount,
            'invoice_no' => $this->response->merchantInvoiceNumber,
            'receiver_bkash_no' => $this->response->receiverMSISDN,
            'b2cfee' => (int)$this->response->b2cFee
        );
    }

    public function getError()
    {
        return array(
            'code' => (int)$this->response->errorCode,
            'message' => $this->response->errorMessage
        );
    }
}
