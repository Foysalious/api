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

    public function getTransactionDetails()
    {
        return $this->transactionDetails;
    }

    public function isPending()
    {
        return $this->isPending;
    }
}
