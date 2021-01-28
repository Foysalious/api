<?php namespace Sheba\TopUp\Vendor\Response;


class TopUpSuccessResponse
{
    private $transactionId;
    private $transactionDetails;
    private $topUpStatus;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function getTopUpStatus()
    {
        return $this->topUpStatus;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getTransactionDetailsAsString()
    {
        return json_encode($this->transactionDetails);
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        if (is_object($this->transactionDetails) && isset($this->transactionDetails->MESSAGE)) {
            return $this->transactionDetails->MESSAGE;
        } else {
            return null;
        }
    }
}