<?php namespace Sheba\PaymentLink;

class  PaymentLinkStatics
{
    const SERVICE_CHARGE = 2.0;

    public static function faq_webview(): string
    {
        return config('sheba.partners_url') . "/api/payment-link-faq";
    }

    public static function payment_setup_faq_webview(): string
    {
        return config('sheba.partners_url') . "/api/payment-setup-faq";
    }


    public static function get_payment_link_tax()
    {
        return config('payment_link.payment_link_tax');
    }

    public static function get_payment_link_commission()
    {
        return config('payment_link.payment_link_commission');
    }

    public static function get_transaction_message(): string
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

    public static function customPaymentServiceData(): array
    {
        return [
            "step"                           => self::get_step_margin(),
            "minimum_percentage"             => self::get_minimum_percentage(),
            "maximum_percentage"             => self::get_maximum_percentage(),
            "terms_and_condition"            => self::faq_webview()
        ];
    }

    public static function paymentTermsAndConditionWebview(): string
    {
        return config('sheba.partners_base_url') . "/" . "payment-solution-terms-condition";
    }

    public static function paidByTypes(): array
    {
        return ['partner', 'customer'];
    }
}
