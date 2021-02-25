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
    const DUE_PAYMENT_REQUEST        = 'due_payment_request';
    const PARTNER_RENEWAL            = 'partner_renewal';
    const SEND_DUE_DEPOSIT_TO_CUSTOMER  = 'send_due_deposit_to_customer';
    const AFFILIATE_BONUS            = 'affiliate_bonus';
    const INSUFFICIENT_NOTIFICATION  = 'insufficient_notification';
    const PARTNER_ORDER_REQUEST      = 'partner_order_request';
    const TRIP_REQUEST_ACCEPT        = 'trip_request_accept';
    const PARTNER_AFFILIATION        = 'partner_affiliation';
    const TRANSPORT_TICKET_CONFIRM   = 'transport_ticket_confirm';
    const PARTNER_REFERRAL           = 'partner_referral';
    const PARTNER_SUBSCRIPTION       = 'partner_subscription';
    const CHECKOUT                   = 'checkout';
    const SMS_CAMPAIGN               = 'sms_campaign';
}