<?php


namespace Sheba\ExternalPaymentLink\Statics;


class ExternalPaymentStatics
{
    public static function getPaymentInitiateValidator()
    {
        $min = self::minimumAmount();
        $max = self::maximumAmount();
        return [
            'amount'          => "required|numeric|min:$min|max:$max",
            'transaction_id'  => 'required',
            'success_url'     => 'required|url',
            'fail_url'        => 'required|url',
            'customer_mobile' => 'sometimes|mobile:bd',
            'customer_name'   => 'required_with:customer_mobile|string',
            'emi_month'       => 'sometimes|integer|in:' . implode(',', config('emi.valid_months')),
            'payment_details' => 'sometimes|string',
            'purpose'         => 'sometimes|string'
        ];
    }

    public static function dataFields()
    {
        return array_keys(self::getPaymentInitiateValidator());
    }

    public static function minimumAmount()
    {
        return config('external_payment_link.minimum_amount');
    }

    public static function maximumAmount()
    {
        return config('external_payment_link.maximum_amount');
    }

    public static function getPurpose($data,$client){
        return isset($data['purpose'])&&!empty($data['purpose']) ? $data['purpose'] : $client->default_purpose?:'E-Com Payments';
    }
}
