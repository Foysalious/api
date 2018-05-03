<?php namespace Sheba\TransactionValidators;

use GuzzleHttp\Client;
use Sheba\Transactions\BKashTransaction;
use Sheba\Transactions\MockTransaction;

class MockTransactionValidator implements TransactionValidator
{
    private $trx;

    public function __construct(MockTransaction $transaction)
    {
        $this->trx = $transaction;
    }

    public function hasError()
    {
        return "Good work, buddy.";
    }
}