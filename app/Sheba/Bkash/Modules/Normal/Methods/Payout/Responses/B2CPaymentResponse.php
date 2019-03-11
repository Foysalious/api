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
        return $this->response == 'Completed';
    }

    public function getSuccess()
    {
        return array(
            'completed_time' => $this->response->completedTimebkash,
            'trxID' => $this->response->trxIDbkash,
            'status' => $this->response->transactionStatusbkash,
            'amount' => $this->response->amountbkash,
            'invoice_no' => $this->response->merchantInvoiceNumberbkash,
            'receiver_bkash_no' => $this->response->receiverMSISDNbkash,
            'b2cfee' => $this->response->b2cFee
        );
    }

    public function getError()
    {
        return array(
            'code' => $this->response->errorCode,
            'message' => $this->response->errorMessage
        );
    }
}