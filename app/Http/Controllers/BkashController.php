<?php

namespace App\Http\Controllers;


use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Redis;

class BkashController extends Controller
{
    private $appKey = "5tunt4masn6pv2hnvte1sb5n3j";
    private $appSecret = "1vggbqd4hqk9g96o9rrrp2jftvek578v7d2bnerim12a87dbrrka";
    private $username = "sandboxTestUser";
    private $password = "hWD@8vtzw0";

    public function create(Request $request)
    {
        try {
            $token = Redis::get('BKASH_TOKEN');
            $token = $token ? $token : $this->grantToken();
            $invoice = "SHEBA_TEST_" . str_random(4);
            $intent = "sale";
            $create_pay_body = json_encode(array(
                'amount' => (double)$request->amount,
                'currency' => 'BDT',
                'intent' => $intent,
                'merchantInvoiceNumber' => $invoice
            ));
            $url = curl_init('https://checkout.sandbox.bka.sh/v1.0.0-beta/checkout/payment/create');
            $header = array(
                'Content-Type:application/json',
                'authorization:' . $token,
                'x-app-key:' . $this->appKey);
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $create_pay_body);
            $result_data = curl_exec($url);
            curl_close($url);
            return api_response($request, $result_data, 200, ['data' => json_decode($result_data)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function execute(Request $request)
    {
        try {
            $token = Redis::get('BKASH_TOKEN');
            $token = $token ? $token : $this->grantToken();
            $url = curl_init('https://checkout.sandbox.bka.sh/v1.0.0-beta/checkout/payment/execute/' . $request->payment_id);
            $header = array(
                'authorization:' . $token,
                'x-app-key:' . $this->appKey);
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            $resultdatax = curl_exec($url);
            curl_close($url);
            return api_response($request, $resultdatax, 200, ['data' => $resultdatax]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function grantToken()
    {
        try {
            $post_token = array(
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret
            );
            $url = curl_init('https://checkout.sandbox.bka.sh/v1.0.0-beta/checkout/token/grant');
            $post_token = json_encode($post_token);
            $header = array(
                'Content-Type:application/json',
                'password:' . $this->password,
                'username:' . $this->username);
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $result_data = curl_exec($url);
            curl_close($url);
            $data = json_decode($result_data, true);
            $token = $data['id_token'];
            Redis::set('BKASH_TOKEN', $token);
            Redis::expire('BKASH_TOKEN', (int)$data['expires_in'] - 100);
            return $token;
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}