<?php namespace Sheba\Payment\Methods;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Error\SslErrorAdapter;
use Cache;
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
        $invoice = "SHEBA_SSL_" . strtoupper($payable->type) . '_' . $payable->id . '_' . Carbon::now()->timestamp;
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
        $result = $this->getSslSession($data);
        if ($result && $result->status == 'SUCCESS') {
            $payment = new Payment();
            DB::transaction(function () use ($payment, $payable, $invoice, $result, $user) {
                $payment->payable_id = $payable->id;
                $payment->transaction_id = $invoice;
                $payment->status = 'initiated';
                $payment->transaction_details = json_encode($result);
                $payment->redirect_url = $result->GatewayPageURL;
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
            return $payment;
        } else {
            return null;
        }
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

    public function getError(): PayChargeMethodError
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