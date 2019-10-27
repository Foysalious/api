<?php namespace Sheba\FraudDetection;


use Sheba\Helpers\ConstGetter;

class PaymentMethod
{
    use ConstGetter;

    const BKASH = 'bkash';
    const SSL = 'ssl';
    const CITY_BANK = 'city_bank';
    const TOP_UP = 'top_up';
    const SERVICE_PURCHASE = 'service_purchase';
    const MOVIE = 'movie';
    const TRANSPORT = 'transport';
    const WITHDRAW_REQUEST = 'withdraw_request';
    const BANK = 'bank';
}
