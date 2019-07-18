<?php namespace Sheba\PaymentLink;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class PaymentLinkClient
{
    public function paymentLinkList(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;

            $url = config('sheba.payment_link_url') . '/api/v1/payment-links';
            $url = "$url?userType=$user_type&userId=$user_id";
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['links'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}