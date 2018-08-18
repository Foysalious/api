<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PartnerOrder;
use Illuminate\Http\Request;
use Redis;
use Sheba\OnlinePayment\Bkash;
use Sheba\OnlinePayment\Payment;

class BkashController extends Controller
{
    private $appKey = "5tunt4masn6pv2hnvte1sb5n3j";
    private $appSecret = "1vggbqd4hqk9g96o9rrrp2jftvek578v7d2bnerim12a87dbrrka";
    private $username = "sandboxTestUser";
    private $password = "hWD@8vtzw0";

    public function create($customer, Request $request)
    {
        try {
            $payment = new Payment((Job::find((int)$request->job_id))->partnerOrder->order, new Bkash());
            $result = [];
            $query = parse_url($payment->generateLink(1))['query'];
            parse_str($query, $result);
            $key_name = $result['paymentID'];
            $payment_info = Redis::get("$key_name");
            $payment_info = json_decode($payment_info);
            return api_response($request, $result, 200, ['data' => $payment_info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function execute(Request $request)
    {
        try {
            $payment_info = Redis::get("$request->paymentID");
            $payment_info = json_decode($payment_info);
            $partnerOrder = PartnerOrder::find((int)$payment_info->partner_order_id);
            $payment = new Payment($partnerOrder->order, new Bkash());
            if ($payment->success($request)) {
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function grantToken()
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

    public function getPaymentInfo($paymentID, Request $request)
    {
        try {
            $data = Redis::get("$paymentID");
            return $data ? api_response($request, $data, 200, ['data' => json_decode($data)]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}