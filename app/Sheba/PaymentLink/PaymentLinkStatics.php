<?php namespace Sheba\PaymentLink;

class  PaymentLinkStatics
{
    const PAYMENT_LINK_TYPE_EMI = "emi";
    const PAYMENT_LINK_TYPE_DIGITAL_COLLECTION = "digital_collection";

    public static function faq_webview()
    {
        return config('sheba.partners_url') . "/api/payment-link-faq";
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

    public static function get_step_margin()
    {
        return config('payment_link.step_margin');
    }

    public static function get_minimum_percentage()
    {
        return config('payment_link.minimum_percentage');
    }

    public static function get_maximum_percentage()
    {
        return config('payment_link.maximum_percentage');
    }

    public static function customPaymentLinkData()
    {
        return [
            "step"                           => self::get_step_margin(),
            "minimum_percentage"             => self::get_minimum_percentage(),
            "maximum_percentage"             => self::get_maximum_percentage(),
            "transaction_message"            => self::get_transaction_message(),
            "payment_link_tax"               => self::get_payment_link_tax(),
            "payment_link_charge_percentage" => self::get_payment_link_commission()
        ];
    }

    public static function paidByTypes()
    {
        return ['partner', 'customer'];
    }
}
