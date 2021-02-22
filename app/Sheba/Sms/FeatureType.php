<?php namespace App\Sheba\Sms;

use Sheba\Helpers\ConstGetter;

class FeatureType
{
    use ConstGetter;

    const TOP_UP                     = 'topup';
    const PAYMENT_LINK               = 'payment_link';
    const DUE_TRACKER                = 'due_tracker';
    const PARTNER_REGISTRATION       = 'partner_registration';
}