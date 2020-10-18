<?php


namespace Sheba\ExternalPaymentLink\Statics;


class ExternalPaymentStatics
{
    public static function getPaymentInitiateValidator()
    {
        return [
            'amount'          => 'required|numeric|min:10|max:100000',
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

    public static function getPurpose($data,$client){
        return isset($data['purpose'])&&!empty($data['purpose']) ? $data['purpose'] : $client->default_purpose?:'E-Com Payments';
    }
}
