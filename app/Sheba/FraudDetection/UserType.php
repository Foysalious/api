<?php namespace Sheba\FraudDetection;


use Sheba\Helpers\ConstGetter;

class UserType
{
    use ConstGetter;

    const BONDHU = 'bondhu';
    const PARTNER = 'partner';
    const CUSTOMER = 'customer';
    const BUSINESS = 'business';
    const VENDOR = 'vendor';
    const LOGISTIC = 'logistic';
}
