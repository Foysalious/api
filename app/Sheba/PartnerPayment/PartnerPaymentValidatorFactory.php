<?php namespace Sheba\PartnerPayment;

use Sheba\Transactions\BKashTransaction;
use Sheba\TransactionValidators\BKashTransactionValidator;

class PartnerPaymentValidatorFactory
{
    /**
     * @param $data
     * @return \Sheba\TransactionValidators\TransactionValidator
     */
    public static function make($data)
    {
        if($data['type'] == "bkash") {
            $trx = new BKashTransaction($data['transaction_id'], $data['account'], $data['amount']);
            return new BKashTransactionValidator($trx);
        }
        throw new \InvalidArgumentException("Invalid transaction type.");
    }
}