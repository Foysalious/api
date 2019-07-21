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

    public function defaultPaymentLink(Request $request)
    {
        try {
            $user_type = $request->type;
            $user_id = $request->user->id;

            $url = config('sheba.payment_link_url') . '/api/v1/payment-links';
            $url = "$url?userType=$user_type&userId=$user_id&isDefault=1";
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['links'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function storePaymentLink(Request $request)
    {
        try {
            $data = [
                'amount' => $request->has('isDefault') ? 0 : $request->amount,
                'reason' => $request->purpose,
                'isDefault' => $request->has('isDefault') ? $request->isDefault : 0,
                'userId' => $request->user->id,
                'userName' => $request->user->name,
                'userType' => $request->type,
            ];
            if ($request->has('isDefault')) unset($data['reason']);
            $url = config('sheba.payment_link_url') . '/api/v1/payment-links';
            $client = new Client();
            $response = $client->request('POST', $url, ['form_params' => $data]);
            $response = json_decode($response->getBody());
            if ($response->code == 200)
                return $response->link;
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function paymentLinkStatusChange($link, Request $request)
    {
        try {
            if ($request->status == 'active') {
                $status = 1;
            } else {
                $status = 0;
            }

            $url = config('sheba.payment_link_url') . '/api/v1/payment-links/' . $link;
            $url = "$url?isActive=$status";
            $client = new Client();
            $response = $client->request('PUT', $url, []);
            $response = json_decode($response->getBody());
            if ($response->code == 200)
                return $response;
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function paymentLinkDetails($link, Request $request)
    {
        try {
            $url = config('sheba.payment_link_url') . '/api/v1/payment-links/' . $link;
            $response = (new Client())->get($url)->getBody()->getContents();
            $response = json_decode($response, 1);
            if ($response['code'] == 200)
                return $response['link'];
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}