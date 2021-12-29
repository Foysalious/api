<?php namespace Sheba\PaymentService;

class PaymentServiceStatics
{
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

    public static function customPaymentServiceData()
    {
        return [
            "step"                           => self::get_step_margin(),
            "minimum_percentage"             => self::get_minimum_percentage(),
            "maximum_percentage"             => self::get_maximum_percentage(),
        ];
    }
}