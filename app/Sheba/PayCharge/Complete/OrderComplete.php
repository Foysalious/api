<?php

namespace Sheba\PayCharge\Complete;


use App\Models\Customer;
use App\Models\PartnerOrder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\PayCharge\PayChargable;
use Sheba\RequestIdentification;

class OrderComplete extends PayChargeComplete
{
    CONST CASHBACK_PERCENTAGE = 1;

    public function complete(PayChargable $pay_chargable, $method_response)
    {
        try {
            $client = new Client();
            $partnerOrder = PartnerOrder::find((int)$pay_chargable->id);
            /** @var Customer $customer */
            $customer = Customer::find((int)$pay_chargable->userId);
            $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partnerOrder->id . '/collect',
                [
                    'form_params' => array_merge([
                        'customer_id' => $customer->id,
                        'remember_token' => $customer->remember_token,
                        'sheba_collection' => (double)$pay_chargable->amount,
                        'payment_method' => $method_response['name'],
                        'created_by_type' => 'App\\Models\\Customer',
                        'transaction_detail' => json_encode($method_response['details'])
                    ], (new RequestIdentification())->get())
                ]);
            $response = json_decode($res->getBody());
            if ($response->code == 200) {
                if (strtolower($method_response['name']) == 'wallet') {
                    $amount = ($pay_chargable->amount * self::CASHBACK_PERCENTAGE) / 100;
                    $customer->rechargeWallet($amount, [
                        'amount' => $amount, 'transaction_details' => json_encode($method_response['details']),
                        'type' => 'Credit', 'log' => 'Bonus Sheba Credit'
                    ]);
                }
                return true;
            } else return false;
        } catch (RequestException $e) {
            throw $e;
        }
    }
}