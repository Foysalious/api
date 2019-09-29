<?php namespace Sheba\PartnerPayment;

use Sheba\Transactions\BKashTransaction;
use Sheba\TransactionValidators\BKashTransactionValidator;
use Sheba\Transactions\MockTransaction;
use Sheba\TransactionValidators\MockTransactionValidator;
use Sheba\TransactionValidators\TransactionValidator;

class PartnerPaymentValidatorFactory
{
    /**
     * @param $data
     * @return TransactionValidator
     */
    public static function make($data)
    {
        if($data['type'] == "bkash") {
            $trx = new BKashTransaction($data['transaction_id']);
            return new BKashTransactionValidator($trx);
        }
        if($data['type'] == "mock") {
            $trx = new MockTransaction($data['transaction_id']);
            return new MockTransactionValidator($trx);
        }
        throw new \InvalidArgumentException("Invalid transaction type.");
    }
}