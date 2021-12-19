<?php

namespace App\Http\Controllers;

use App\Models\PartnerOrder;
use App\Sheba\Checkout\OnlinePayment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class OnlinePaymentController extends Controller
{
    public function success(Request $request)
    {
        try {
            if ($this->sslIpnHashValidation($request)) {
                if ($result = $this->validateOrder($request)) {
                    $transaction_id = $request->tran_id;
                    $transaction = Redis::get($transaction_id);
                    $transaction = json_decode($transaction);
                    if ($result->status == "VALID" || $result->status == "VALIDATED") {
                        $online_payment = new OnlinePayment();
                        $result = $online_payment->clearPayment($transaction, $result, $request);
                        if ($result) {
                            $transaction->success = 1;
                            Redis::set($transaction_id, json_encode($transaction));
                            Redis::expire($transaction_id, 7200);
                            return redirect($result);
                        } else {
                            $transaction->message = $online_payment->message;
                            $transaction->result = $result;
                            Redis::set($transaction_id, json_encode($transaction));
                            Redis::expire($transaction_id, 7200);
                            return redirect(env('SHEBA_FRONT_END_URL'));
                        }
                    } else {
                        $transaction->message = "Result status invalid";
                        $transaction->result = $result;
                        Redis::set($transaction_id, json_encode($transaction));
                        Redis::expire($transaction_id, 7200);
                        return redirect(env('SHEBA_FRONT_END_URL'));
                    }
                }
                return redirect(env('SHEBA_FRONT_END_URL'));
            }
            return api_response($request, null, 400);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function fail(Request $request)
    {
        try {
            if ($this->sslIpnHashValidation($request)) {
                $transaction_id = $request->tran_id;
                $transaction = Redis::get($transaction_id);
                $transaction = json_decode($transaction);
                return redirect(strtok((new OnlinePayment())->generateRedirectLink(PartnerOrder::find((int)$transaction->partner_order_id), (int)$transaction->isAdvancedPayment), '?'));
            } else {
                return redirect(env('SHEBA_FRONT_END_URL'));
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function sslIpnHashValidation($request)
    {
        if ($request->filled('verify_key') && $request->verify_sign) {
            $pre_define_key = explode(',', $request->verify_key);
            $new_data = array();
            if (!empty($pre_define_key)) {
                foreach ($pre_define_key as $value) {
                    if (isset($request->$value)) {
                        $new_data[$value] = $request->$value;
                    }
                }
            }
            $new_data['store_passwd'] = md5(config('ssl.store_password'));
            ksort($new_data);
            $hash_string = "";
            foreach ($new_data as $key => $value) {
                $hash_string .= $key . '=' . ($value) . '&';
            }
            $hash_string = rtrim($hash_string, '&');
            if (md5($hash_string) == $request->verify_sign) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function validateOrder($request)
    {
        try {
            $client = new Client();
            $result = $client->request('GET', config('ssl.order_validation_url'), ['query' => [
                'val_id' => $request->val_id,
                'store_id' => config('ssl.store_id'),
                'store_passwd' => config('ssl.store_password'),
            ]]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}