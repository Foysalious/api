<?php namespace Sheba\Transactions;

class BKashTransaction
{
    private $id;
    private $account;
    private $amount;

    public function __construct($transaction_id)
    {
        $this->id = $transaction_id;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}