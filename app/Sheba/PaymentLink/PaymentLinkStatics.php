<?php namespace Sheba\PaymentLink;

class  PaymentLinkStatics
{
    public static function faq_webview()
    {
        return config('sheba.partners_url')."api/emi-faq";
    }

    public static function get_payment_link_tax()
    {
        return config('payment_link.payment_link_tax');
    }

    public static function get_payment_link_commission()
    {
        return config('payment_link.payment_link_commission');
    }

    public static function get_transaction_message()
    {
        $tax        = en2bnNumber(self::get_payment_link_tax());
        $commission = en2bnNumber(self::get_payment_link_commission());
        return "ট্রানজেকশন চার্জ (৳$tax + $commission%)";
    }
}
