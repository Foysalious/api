<?php namespace App\Sheba\Sms;

use Sheba\Helpers\ConstGetter;

class FeatureType
{
    use ConstGetter;

    const TOP_UP                     = 'topup';
    const PAYMENT_LINK               = 'payment_link';
    const DUE_TRACKER                = 'due_tracker';
    const PARTNER_REGISTRATION       = 'partner_registration';
    const LOAN                       = 'loan';
    const COMMON                     = 'common';
    const BUSINESS                   = 'business';
    const PROCUREMENT                = 'procurement';
    const SEND_ORDER_CONFIRMATION    = 'send_order_confirmation';
    const AFFILIATE_FAKE_REFERRAL    = 'affiliate_fake_referral';
    const BID                        = 'bid';
    const POS                        = 'pos';
    const WEB_STORE                  = 'eCom';
    const REGISTRATION               = 'registration';
    const MARKET_PLACE_ORDER         = 'market_place_order';
}