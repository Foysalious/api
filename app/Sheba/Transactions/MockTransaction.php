<?php namespace Sheba\Transactions;


class MockTransaction
{
    private $id;
    private $account;
    private $amount;

    public function __construct($transaction_id, $account, $amount)
    {
        $this->id = $transaction_id;
        $this->account = $account;
        $this->amount = $amount;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}