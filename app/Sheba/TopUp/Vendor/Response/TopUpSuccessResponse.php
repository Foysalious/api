<?php namespace Sheba\TopUp\Vendor\Response;


class TopUpSuccessResponse
{
    private $transactionId;
    private $transactionDetails;
    private $isPending;

    /**
     * @param mixed $transactionId
     * @return TopUpSuccessResponse
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @param mixed $transactionDetails
     * @return TopUpSuccessResponse
     */
    public function setTransactionDetails($transactionDetails)
    {
        $this->transactionDetails = $transactionDetails;
        return $this;
    }

    /**
     * @param bool $isPending
     * @return TopUpSuccessResponse
     */
    public function setIsPending($isPending)
    {
        $this->isPending = $isPending;
        return $this;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getTransactionDetailsAsString()
    {
        return json_encode($this->transactionDetails);
    }

    public function isPending()
    {
        return $this->isPending;
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
