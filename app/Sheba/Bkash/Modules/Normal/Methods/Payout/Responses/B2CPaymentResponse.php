<?php

namespace Sheba\Bkash\Modules\Normal\Methods\Payout\Responses;


class B2CPaymentResponse
{
    private $completedTimebkash;
    private $trxIdBkash;
    private $transactionStatusBkash;
    private $amountBkash;
    private $currencyBkash;
    private $merchantInvoiceNumberBkash;
    private $receiverMSISDNBkash;
    private $b2cFee;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setResponse($response)
    {
        $this->completedTimebkash = $response->completedTimebkash;
        $this->trxIdBkash = $response->trxIDbkash;
        $this->transactionStatusBkash = $response->transactionStatusbkash;
        $this->amountBkash = $response->amountbkash;
        $this->currencyBkash = $response->currencybkash;
        $this->merchantInvoiceNumberBkash = $response->merchantInvoiceNumberbkash;
        $this->receiverMSISDNBkash = $response->receiverMSISDNbkash;
        $this->b2cFee = $response->b2cFee;
        return $this;
    }
}