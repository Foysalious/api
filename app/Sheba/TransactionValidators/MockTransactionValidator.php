<?php namespace Sheba\TransactionValidators;

use GuzzleHttp\Client;
use Sheba\Transactions\BKashTransaction;
use Sheba\Transactions\MockTransaction;

class MockTransactionValidator implements TransactionValidator
{
    private $trx;
    private $amount;

    public function __construct(MockTransaction $transaction)
    {
        $this->trx = $transaction;
        $this->amount = $this->trx->amount;
    }

    public function hasError()
    {
        return false;
    }
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}