<?php namespace Sheba\FraudDetection;

use Sheba\Helpers\ConstGetter;

class TransactionSources
{
    use ConstGetter;

    const BKASH            = 'bkash';
    const SSL              = 'ssl';
    const CITY_BANK        = 'city_bank';
    const TOP_UP           = 'top_up';
    const SERVICE_PURCHASE = 'service_purchase';
    const MOVIE            = 'movie';
    const TRANSPORT        = 'transport';
    const WITHDRAW_REQUEST = 'withdraw_request';
    const BANK             = 'bank';
    const SMS              = 'sms';
    const SHEBA_WALLET     = 'sheba_wallet';
    const BONUS            = 'bonus';
    const PAYMENT_LINK     = 'payment_link';
    const LOAN_FEE         = 'loan_fee';
    const LOAN             = 'loan';
    const LOAN_REPAYMENT   = 'loan_repayment';
}
