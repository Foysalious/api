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
    const AFFILIATE_FAKE_REFERRAL    = 'affiliate_fake_referral';
    const BID                        = 'bid';
    const POS                        = 'pos';
    const WEB_STORE                  = 'eCom';
    const REGISTRATION               = 'registration';
    const MARKET_PLACE_ORDER         = 'market_place_order';
    const PARTNER_RENEWAL            = 'partner_renewal';
    const AFFILIATE_BONUS            = 'affiliate_bonus';
    const PARTNER_SUBSCRIPTION_ORDER_REQUEST      = 'partner_subscription_order';
    const TRIP_REQUEST_ACCEPT        = 'trip_request_accept';
    const PARTNER_AFFILIATION        = 'partner_affiliation';
    const TRANSPORT_TICKET           = 'transport_ticket';
    const PARTNER_REFERRAL           = 'partner_referral';
    const PARTNER_SUBSCRIPTION       = 'partner_subscription';
    const SMS_CAMPAIGN               = 'sms_campaign';
    const INVITE_VENDORS             = 'invite_vendors';
    const MARKETING                  = 'marketing';
}