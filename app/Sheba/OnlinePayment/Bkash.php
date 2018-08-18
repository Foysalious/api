<?php


namespace Sheba\OnlinePayment;

use App\Models\Order;
use Illuminate\Http\Request;
use Redis;

class Bkash implements PaymentGateway
{
    private $appKey = "5tunt4masn6pv2hnvte1sb5n3j";
    private $appSecret = "1vggbqd4hqk9g96o9rrrp2jftvek578v7d2bnerim12a87dbrrka";
    private $username = "sandboxTestUser";
    private $password = "hWD@8vtzw0";
    private $createPaymentUrl = "https://checkout.sandbox.bka.sh/v1.0.0-beta/checkout/payment/create";

    public function generateLink(Order $order, $isAdvancePayment)
    {
        $data = $this->create($order);
        $key_name = $data->paymentID;
        $data->customer_id = $order->customer->id;
        $data->remember_token = $order->customer->remember_token;
        $data->partner_order_id = $order->partnerOrders[0]->id;
        Redis::set($key_name, json_encode($data));
        Redis::expire($key_name, 2 * 60 * 60);
        return config('sheba.front_url') . '/bkash?paymentID=' . $key_name;
    }

    private function create(Order $order)
    {
        try {
            $partnerOrder = $order->partnerOrders[0];
            $partnerOrder->calculate(true);
            $token = Redis::get('BKASH_TOKEN');
            $token = $token ? $token : $this->grantToken();
            $invoice = "SHEBA_BKASH_PAYMENT_" . $partnerOrder->id . '_' . str_random(4);
            $intent = "sale";
            $create_pay_body = json_encode(array(
                'amount' => (double)$partnerOrder->due,
                'currency' => 'BDT',
                'intent' => $intent,
                'merchantInvoiceNumber' => $invoice
            ));
            $url = curl_init($this->createPaymentUrl);
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

    public function execute($paymentID)
    {
        try {
            $token = Redis::get('BKASH_TOKEN');
            $token = $token ? $token : $this->grantToken();
            $url = curl_init('https://checkout.sandbox.bka.sh/v1.0.0-beta/checkout/payment/execute/' . $paymentID);
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

    public function success(Request $request)
    {
        try {
            $payment_info = Redis::get("$request->paymentID");
            $payment_info = json_decode($payment_info);
            $result_data = $this->execute($request->paymentID);
            if ($result_data->transactionStatus = "Completed" && (double)$result_data->amount == (double)$payment_info->amount) {
                return $result_data;
            } else {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function formatTransactionData($gateway_response)
    {
        return json_encode(array(
            'transaction_id' => $gateway_response->trxID,
            'gateway' => "bkash",
            'details' => $gateway_response
        ));
    }
}