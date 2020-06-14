<?php namespace Sheba\Payment\Methods\Ssl;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use GuzzleHttp\Client;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Ssl\Response\InitResponse;
use Sheba\Payment\Methods\Ssl\Response\ValidationResponse;
use Sheba\Payment\Methods\Ssl\Stores\SslStore;
use Sheba\Payment\Statuses;
use Sheba\RequestIdentification;
use DB;

class Ssl extends PaymentMethod
{
    /** @var SslStore */
    private $store;

    private $successUrl;
    private $failUrl;
    private $cancelUrl;
    CONST NAME        = 'ssl';
    CONST NAME_DONATE = 'ssl_donation';
    private $isDonate = false;

    public function __construct()
    {
        parent::__construct();
        $this->successUrl         = config('payment.ssl.urls.success');
        $this->failUrl            = config('payment.ssl.urls.fail');
        $this->cancelUrl          = config('payment.ssl.urls.cancel');
    }

    public function setStore(SslStore $store)
    {
        $this->store = $store;
        return $this;
    }

    public function forDonation()
    {
        $this->isDonate = true;
        return $this;
    }

    /**
     * @param Payable $payable
     * @return Payment
     * @throws \Exception
     */
    public function init(Payable $payable): Payment
    {
        $invoice              = "SHEBA_SSL_" . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . randomString(10, 1, 1);
        $data                 = array();
        $data['store_id']     = $this->store->getStoreId();
        $data['store_passwd'] = $this->store->getStorePassword();
        $data['total_amount'] = (double)$payable->amount;
        $data['currency']     = "BDT";
        $data['success_url']  = $this->successUrl;
        $data['fail_url']     = $this->failUrl;
        $data['cancel_url']   = $this->cancelUrl;
        $data['tran_id']      = $invoice;
        $user                 = $payable->user;
        $data['cus_name']     = $payable->getName();
        $data['cus_email']    = $payable->getEmail();
        $data['cus_phone']    = $payable->getMobile();
        if ($payable->amount >= config('sheba.min_order_amount_for_emi')) {
            $data['emi_option']          = 1;
            $data['emi_max_inst_option'] = 12;
            if ($payable->emi_month) {
                $data['emi_selected_inst'] = (int)$payable->emi_month;
                $data['emi_allow_only']    = 1;
            }
        }
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice, $user) {
            $payment->payable_id             = $payable->id;
            $payment->transaction_id         = $invoice;
            $payment->gateway_transaction_id = $invoice;
            $payment->status                 = Statuses::INITIATED;
            $payment->valid_till             = $this->getValidTill();
            $this->setModifier($user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details             = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method     = $this->getMethodName();
            $payment_details->amount     = $payable->amount;
            $payment_details->save();
        });
        $response      = $this->getSslSession($data);
        $init_response = new InitResponse();
        $init_response->setResponse($response);
        if ($init_response->hasSuccess()) {
            $success                      = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->redirect_url        = $success->redirect_url;
        } else {
            $error = $init_response->getError();
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to'                  => Statuses::INITIATION_FAILED,
                'from'                => $payment->status,
                'transaction_details' => json_encode($error->details)
            ]);
            $payment->status              = Statuses::INITIATION_FAILED;
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    public function getSslSession($data)
    {
        $client = new Client();
        $result = $client->request('POST', $this->store->getSessionUrl(), ['form_params' => $data]);
        return json_decode($result->getBody());
    }

    public function validate(Payment $payment)
    {
        if ($this->sslIpnHashValidation()) {
            $validation_response = new ValidationResponse();
            $validation_response->setResponse($this->validateOrder());
            $validation_response->setPayment($payment);
            $this->paymentLogRepo->setPayment($payment);
            if ($validation_response->hasSuccess()) {
                $success = $validation_response->getSuccess();
                $this->paymentLogRepo->create([
                    'to'                  => Statuses::VALIDATED,
                    'from'                => $payment->status,
                    'transaction_details' => $payment->transaction_details
                ]);
                $payment->status              = Statuses::VALIDATED;
                $payment->transaction_details = json_encode($success->details);
            } else {
                $error = $validation_response->getError();
                $this->paymentLogRepo->create([
                    'to'                  => Statuses::VALIDATION_FAILED,
                    'from'                => $payment->status,
                    'transaction_details' => $payment->transaction_details
                ]);
                $payment->status              = Statuses::VALIDATION_FAILED;
                $payment->transaction_details = json_encode($error->details);
            }
        } else {
            $request           = request()->all();
            $request['status'] = 'HASH_VALIDATION_FAILED';
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to'                  => Statuses::VALIDATION_FAILED,
                'from'                => $payment->status,
                'transaction_details' => $payment->transaction_details
            ]);
            $payment->status              = Statuses::VALIDATION_FAILED;
            $payment->transaction_details = json_encode($request);
        }
        $payment->update();
        return $payment;
    }

    private function sslIpnHashValidation()
    {
        if (request()->has('verify_key') && request()->has('verify_sign')) {
            $pre_define_key = explode(',', request('verify_key'));
            $new_data       = array();
            if (!empty($pre_define_key)) {
                foreach ($pre_define_key as $value) {
                    if (request()->exists($value)) {
                        $new_data[$value] = request($value);
                    }
                }
            }
            $new_data['store_passwd'] = md5($this->store->getStorePassword());
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
        $client   = new Client();
        try {
            $result   = $client->request('GET', $this->store->getOrderValidationUrl(), [
                'query' => [
                    'val_id'       => request('val_id'),
                    'store_id'     => $this->store->getStoreId(),
                    'store_passwd' => $this->store->getStorePassword(),
                ]
            ]);
            $response = json_decode($result->getBody()->getContents());
            if (!$response) {

                $response = new \stdClass();
                $response->status  = "ERROR";
                $response->result  = $result->getBody()->getContents();
                $response->code    = 502;
                $response->tran_id = null;
            }
        } catch (\Throwable $e) {
            $response = new \stdClass();
            $response->status  = "ERROR";
            $response->result  = $e->getMessage();
            $response->code    = $e->getCode();
            $response->trace   = $e->getTrace();
            $response->tran_id = null;
        }
        return $response;
    }

    public function getMethodName()
    {
        return $this->isDonate ? self::NAME_DONATE : self::NAME;
    }
}
