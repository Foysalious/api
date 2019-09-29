<?php namespace Sheba\Transactions;


class MockTransaction
{
    private $id;
    private $amount;

    public function __construct($transaction_id)
    {
        $this->id = $transaction_id;
        $this->amount = 10;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}