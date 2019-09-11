<?php namespace Sheba\Subscription\Partner;

use Sheba\Helpers\ConstGetter;

class BillingType
{
    use ConstGetter;

    const MONTHLY = 'monthly';
    const HALF_YEARLY = 'half_yearly';
    const YEARLY = 'yearly';
}