<?php

namespace Sheba\PayCharge\Methods;


use GuzzleHttp\Client;
use Sheba\PayCharge\PayChargable;

class BkashOld implements PayChargeMethod
{
    public function init(PayChargable $payChargable)
    {
        return true;
    }

    private function getValidationUrl()
    {
        return config('bkash.verification_endpoint')
            . "?user=" . config('bkash.old_username') . "&pass=" . config('bkash.old_password')
            . "&msisdn=" . config('bkash.merchant_number')
            . "&trxid=" . $this->trx->id;
    }

    public function validate($payment)
    {
        $client = new Client();
        $res = json_decode($client->request('GET', $this->getValidationUrl(), [
            'headers' => ['Content-Type' => 'application/json']
        ])->getBody());
    }

    public function formatTransactionData($method_response)
    {
        // TODO: Implement formatTransactionData() method.
    }

    public function getError(): PayChargeMethodError
    {
        // TODO: Implement getError() method.
    }
}