<?php namespace Sheba\Transactions\DTO;

class ShebaTransaction
{
    private $transactionId;
    private $gateway;
    private $details;

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @param mixed $gateway
     * @return $this
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    public function toArray()
    {
        return [
            'transaction_id' => $this->transactionId,
            'gateway' => $this->gateway,
            'details' => json_decode(json_encode($this->details), 1),
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}