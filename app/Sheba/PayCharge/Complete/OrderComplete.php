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
    public function complete(PayChargable $pay_chargable, $method_response)
    {
        try {
            $client = new Client();
            $partnerOrder = PartnerOrder::find((int)$pay_chargable->id);
            $customer = Customer::find((int)$pay_chargable->userId);
            $res = $client->request('POST', config('sheba.admin_url') . '/api/partner-order/' . $partnerOrder->id . '/collect',
                [
                    'form_params' => array_merge([
                        'customer_id' => $customer->id,
                        'remember_token' => $customer->remember_token,
                        'sheba_collection' => (double)$pay_chargable->amount,
                        'payment_method' => 'Online',
                        'created_by_type' => 'App\Models\Customer',
                        'transaction_detail' => $method_response
                    ], (new RequestIdentification())->get())
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}