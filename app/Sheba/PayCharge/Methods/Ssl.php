<?php namespace Sheba\PayCharge\Methods;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\PayCharge\Adapters\Error\SslErrorAdapter;
use Sheba\PayCharge\PayChargable;
use Cache;

class Ssl implements PayChargeMethod
{
    private $message;
    private $error=[];
    private $storeId;
    private $storePassword;
    private $sessionUrl;
    private $successUrl;
    private $failUrl;
    private $cancelUrl;
    private $orderValidationUrl;

    public function __construct()
    {
        $this->storeId = config('ssl.store_id');
        $this->storePassword = config('ssl.store_password');
        $this->sessionUrl = config('ssl.session_url');
        $this->successUrl = config('ssl.success_url');
        $this->failUrl = config('ssl.fail_url');
        $this->cancelUrl = config('ssl.cancel_url');
        $this->orderValidationUrl = config('ssl.order_validation_url');
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function init(PayChargable $payChargable)
    {
        $invoice = "SHEBA_SSL_" . strtoupper($payChargable->type) . '_' . $payChargable->id . '_' . Carbon::now()->timestamp;
        $data = array();
        $data['store_id'] = $this->storeId;
        $data['store_passwd'] = $this->storePassword;
        $data['total_amount'] = (double)$payChargable->amount;
        $data['currency'] = "BDT";
        $data['success_url'] = $this->successUrl;
        $data['fail_url'] = $this->failUrl;
        $data['cancel_url'] = $this->cancelUrl;
        $data['emi_option'] = 0;
        $data['tran_id'] = $invoice;
        $user = $payChargable->userType;
        $user = $user::find($payChargable->userId);
        $data['cus_name'] = $user->profile->name;
        $data['cus_email'] = $user->profile->email;
        $data['cus_phone'] = $user->profile->mobile;
        $result = $this->getSslSession($data);
        if ($result && $result->status == 'SUCCESS') {
            $result->name = 'online';
            $payment_info = array(
                'transaction_id' => $invoice,
                'id' => $payChargable->id,
                'type' => $payChargable->type,
                'pay_chargable' => serialize($payChargable),
                'link' => $result->GatewayPageURL,
                'method_info' => $result
            );
            Cache::store('redis')->put("paycharge::$invoice", json_encode($payment_info), Carbon::tomorrow());
            array_forget($payment_info, 'pay_chargable');
            array_forget($payment_info, 'method_info');
            return $payment_info;
        }
        return null;
    }

    public function validate($payment)
    {
        if ($this->sslIpnHashValidation()) {
            if ($result = $this->validateOrder()) {
                if ($result->status == "VALID" || $result->status == "VALIDATED") {
                    return $result;
                } else {
                    $this->message = 'Validation Failed. Response status is ' . $result->status;
                    return null;
                }
            }

        }
    }

    public function formatTransactionData($method_response)
    {
        return array(
            'name' => 'Online',
            'details' => array(
                'transaction_id' => $method_response->tran_id,
                'gateway' => "ssl",
                'details' => $method_response
            )
        );
    }

    public function getError(): MethodError
    {
        return (new SslErrorAdapter($this->error))->getError();
    }

    public function getSslSession($data)
    {
        try {
            $client = new Client();
            $result = $client->request('POST', $this->sessionUrl, ['form_params' => $data]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            throw $e;
        }
    }

    private function sslIpnHashValidation()
    {
        if (request()->has('verify_key') && request()->has('verify_sign')) {
            $pre_define_key = explode(',', request('verify_key'));
            $new_data = array();
            if (!empty($pre_define_key)) {
                foreach ($pre_define_key as $value) {
                    if (request()->exists($value)) {
                        $new_data[$value] = request($value);
                    }
                }
            }
            $new_data['store_passwd'] = md5($this->storePassword);
            ksort($new_data);
            $hash_string = "";
            foreach ($new_data as $key => $value) {
                $hash_string .= $key . '=' . ($value) . '&';
            }
            $hash_string = rtrim($hash_string, '&');
            if (md5($hash_string) == request('verify_sign')) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function validateOrder()
    {
        try {
            $client = new Client();
            $result = $client->request('GET', $this->orderValidationUrl, ['query' => [
                'val_id' => request('val_id'),
                'store_id' => $this->storeId,
                'store_passwd' => $this->storePassword,
            ]]);
            return json_decode($result->getBody());
        } catch (RequestException $e) {
            throw $e;
        }
    }
}