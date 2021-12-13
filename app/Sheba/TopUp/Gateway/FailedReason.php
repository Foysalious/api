<?php namespace Sheba\TopUp\Gateway;

abstract class FailedReason
{
    protected $transaction;

    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    abstract public function getReason();
}
