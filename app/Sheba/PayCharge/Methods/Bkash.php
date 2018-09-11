<?php namespace Sheba\PayCharge\Methods;

use Carbon\Carbon;
use Sheba\PayCharge\PayChargable;
use Cache;
use Redis;

class Bkash implements PayChargeMethod
{
    private $appKey;
    private $appSecret;
    private $username;
    private $password;
    private $url;

    public function __construct()
    {
        $this->appKey = config('bkash.app_key');
        $this->appSecret = config('bkash.app_secret');
        $this->username = config('bkash.username');
        $this->password = config('bkash.password');
        $this->url = config('bkash.url');
    }

    public function init(PayChargable $payChargable)
    {
        if ($data = $this->create($payChargable)) {
            $data->name = "bkash";
            $payment_info = array(
                'transaction_id' => $data->merchantInvoiceNumber,
                'id' => $payChargable->id,
                'type' => $payChargable->type,
                'link' => config('sheba.front_url') . '/bkash?paymentID=' . $data->merchantInvoiceNumber,
                'pay_chargable' => serialize($payChargable),
                'method_info' => $data
            );
            Cache::store('redis')->put("paycharge::$data->merchantInvoiceNumber", json_encode($payment_info), Carbon::tomorrow());
            array_forget($payment_info, 'pay_chargable');
            return $payment_info;
        } else {
            return null;
        }
    }

    public function validate($payment)
    {
        try {
            $result_data = $this->execute($payment->method_info->paymentID);
            $pay_chargable = unserialize($payment->pay_chargable);
            if ($result_data->transactionStatus == "Completed" && (double)$result_data->amount == (double)$pay_chargable->amount) {
                return $result_data;
            } else {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function formatTransactionData($method_response)
    {
        return array(
            'name' => 'bkash',
            'details' => array(
                'transaction_id' => $method_response->trxID,
                'gateway' => "bkash",
                'details' => $method_response,
            )
        );
    }

    private function create(PayChargable $payChargable)
    {
        try {
            $token = Redis::get('BKASH_TOKEN');
            $token = $token ? $token : $this->grantToken();
            $invoice = "SHEBA_BKASH_" . strtoupper($payChargable->type) . '_' . $payChargable->id . '_' . Carbon::now()->timestamp;
            $intent = "sale";
            $create_pay_body = json_encode(array(
                'amount' => (double)$payChargable->__get('amount'),
                'currency' => 'BDT',
                'intent' => $intent,
                'merchantInvoiceNumber' => $invoice
            ));
            $url = curl_init($this->url . '/checkout/payment/create');
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
            return json_decode($result_data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function grantToken()
    {
        try {
            $post_token = array(
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret
            );
            $url = curl_init($this->url . '/checkout/token/grant');
            $post_token = json_encode($post_token);
            $header = array(
                'Content-Type:application/json',
                'password:' . $this->password,
                'username:' . $this->username);
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
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

    private function execute($paymentID)
    {
        try {
            $token = Redis::get('BKASH_TOKEN');
            $token = $token ? $token : $this->grantToken();
            $url = curl_init($this->url . '/checkout/payment/execute/' . $paymentID);
            $header = array(
                'authorization:' . $token,
                'x-app-key:' . $this->appKey);
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            $result_data = curl_exec($url);
            $result_data = json_decode($result_data);
            curl_close($url);
            return $result_data;
        } catch (\Throwable $e) {
            return null;
        }
    }
}