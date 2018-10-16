<?php

namespace Sheba\Payment\Methods\Ssl;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Error\SslErrorAdapter;
use Cache;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Ssl\Response\InitResponse;
use Sheba\Payment\Methods\Ssl\Response\ValidationResponse;
use Sheba\RequestIdentification;
use DB;

class Ssl implements PaymentMethod
{
    use ModificationFields;
    private $message;
    private $error = [];
    private $storeId;
    private $storePassword;
    private $sessionUrl;
    private $successUrl;
    private $failUrl;
    private $cancelUrl;
    private $orderValidationUrl;
    CONST NAME = 'ssl';

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

    public function init(Payable $payable): Payment
    {
        $invoice = "SHEBA_SSL_" . strtoupper($payable->readable_type) . '_' . $payable->id . '_' . Carbon::now()->timestamp;
        $data = array();
        $data['store_id'] = $this->storeId;
        $data['store_passwd'] = $this->storePassword;
        $data['total_amount'] = (double)$payable->amount;
        $data['currency'] = "BDT";
        $data['success_url'] = $this->successUrl;
        $data['fail_url'] = $this->failUrl;
        $data['cancel_url'] = $this->cancelUrl;
        $data['emi_option'] = 0;
        $data['tran_id'] = $invoice;
        $user = $payable->user_type;
        $user = $user::find($payable->user_id);
        $data['cus_name'] = $user->profile->name;
        $data['cus_email'] = $user->profile->email;
        $data['cus_phone'] = $user->profile->mobile;
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice, $user) {
            $payment->payable_id = $payable->id;
            $payment->transaction_id = $invoice;
            $payment->status = 'initiated';
            $payment->valid_till = Carbon::tomorrow();
            $this->setModifier($user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method = self::NAME;
            $payment_details->amount = $payable->amount;
            $payment_details->save();

        });
        $response = $this->getSslSession($data);
        $init_response = new InitResponse();
        $init_response->setResponse($response);
        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->redirect_url = $success->redirect_url;
        } else {
            $error = $init_response->getError();
            $payment->status = 'failed';
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
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

    public function validate(Payment $payment)
    {
        if ($this->sslIpnHashValidation()) {
            $validation_response = new ValidationResponse();
            $validation_response->setResponse($this->validateOrder());
            $validation_response->setPayment($payment);
            if ($validation_response->hasSuccess()) {
                $success = $validation_response->getSuccess();
                $payment->status = 'validated';
                $payment->transaction_details = json_encode($success->details);
            } else {
                $error = $validation_response->getError();
                $payment->status = 'validation_failed';
                $payment->transaction_details = json_encode($error->details);
            }
        } else {
            $request = request()->all();
            $request['status'] = 'HASH_VALIDATION_FAILED';
            $payment->status = 'validation_failed';
            $payment->transaction_details = json_encode($request);
        }
        $payment->update();
        return $payment;
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

    public function getError(): PayChargeMethodError
    {
        return (new SslErrorAdapter($this->error))->getError();
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