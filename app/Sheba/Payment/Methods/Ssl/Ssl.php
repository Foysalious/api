<?php namespace Sheba\Payment\Methods\Ssl;

use App\Models\PartnerOrder;
use App\Models\Payable;
use App\Models\Payment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\Payment\Methods\Ssl\Response\InitResponse;
use Sheba\Payment\Methods\Ssl\Response\ValidationResponse;
use Sheba\Payment\Methods\Ssl\Stores\SslStore;
use Sheba\Payment\Statuses;
use DB;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;
use stdClass;

class Ssl extends PaymentMethod
{
    /** @var SslStore */
    private $store;

    private $successUrl;
    private $failUrl;
    private $cancelUrl;
    const NAME = 'ssl';
    const NAME_DONATE = 'ssl_donation';
    private $isDonate = false;

    /** @var TPProxyClient */
    private $tpClient;

    public function __construct(TPProxyClient $tp_client)
    {
        parent::__construct();
        $this->successUrl = config('payment.ssl.urls.success');
        $this->failUrl = config('payment.ssl.urls.fail');
        $this->cancelUrl = config('payment.ssl.urls.cancel');
        $this->tpClient = $tp_client;
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
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $payment = $this->createPayment($payable, $this->store->getName());
        $response = $this->createSslSession($payment);
        $init_response = new InitResponse();
        $init_response->setResponse($response);

        if ($init_response->hasSuccess()) {
            $success = $init_response->getSuccess();
            $payment->transaction_details = json_encode($success->details);
            $payment->redirect_url = $success->redirect_url;
        } else {
            $error = $init_response->getError();
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to' => Statuses::INITIATION_FAILED,
                'from' => $payment->status,
                'transaction_details' => json_encode($error->details)
            ]);
            $payment->status = Statuses::INITIATION_FAILED;
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    /**
     * @param Payment $payment
     * @return mixed
     * @throws TPProxyServerError
     */
    private function createSslSession(Payment $payment)
    {
        /** @var Payable $payable */
        $payable = $payment->payable;
        $data = array();
        $data['store_id'] = $this->store->getStoreId();
        $data['store_passwd'] = $this->store->getStorePassword();
        $data['total_amount'] = (double)$payable->amount;
        $data['currency']     = "BDT";
        $data['success_url']  = $this->successUrl;
        $data['fail_url']     = $this->failUrl;
        $data['cancel_url']   = $this->cancelUrl;
        $data['tran_id']      = $payment->transaction_id;
        if ($payable->isPartnerOrder()) {
            $data['invoice_id']   = $this->getPayableInvoiceId($payable);
            /** @var PartnerOrder $partner_order */
            $partner_order = $payable->getPayableType();
            $job = $partner_order->lastJob();
            $data['no_offer'] = $job->hasDiscount() ? 1 : 0;
        }
        $data['cus_name']     = $payable->getName();
        $data['cus_email']    = $payable->getEmail();
        $data['cus_phone']    = $payable->getMobile();
        $data['product_category'] = $payable->description;
        $this->setEmi($payable,$data);

        $request = (new TPRequest())->setUrl($this->store->getSessionUrl())
            ->setMethod(TPRequest::METHOD_POST)->setInput($data);
        return $this->tpClient->call($request);
    }
    private function setEmi(Payable $payable,&$data){
        if ($payable->completion_type=='payment_link'){
            if ($payable->emi_month > 0) {
                $data['emi_option']          = 1;
                $data['emi_allow_only']    = 1;
                $data['emi_selected_inst'] = (int)$payable->emi_month;
            }
        }else{
            if ($payable->amount >= config('sheba.min_order_amount_for_emi')) {
                $data['emi_option'] = 1;
                $data['emi_max_inst_option'] = 12;
                if ($payable->emi_month) {
                    $data['emi_selected_inst'] = (int)$payable->emi_month;
                    $data['emi_allow_only'] = 1;
                }
            }
        }
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function validate(Payment $payment): Payment
    {
        if ($this->sslIpnHashValidation()) {
            $validation_response = new ValidationResponse();
            $validation_response->setResponse($this->validateOrder());
            $validation_response->setPayment($payment);
            $this->paymentLogRepo->setPayment($payment);
            if ($validation_response->hasSuccess()) {
                $success = $validation_response->getSuccess();
                $this->paymentLogRepo->create([
                    'to' => Statuses::VALIDATED,
                    'from' => $payment->status,
                    'transaction_details' => $payment->transaction_details
                ]);
                $payment->status = Statuses::VALIDATED;
                $payment->transaction_details = json_encode($success->details);
            } else {
                $error = $validation_response->getError();
                $this->paymentLogRepo->create([
                    'to' => Statuses::VALIDATION_FAILED,
                    'from' => $payment->status,
                    'transaction_details' => $payment->transaction_details
                ]);
                $payment->status = Statuses::VALIDATION_FAILED;
                $payment->transaction_details = json_encode($error->details);
            }
        } else {
            $request = request()->all();
            $request['status'] = 'HASH_VALIDATION_FAILED';
            $this->paymentLogRepo->setPayment($payment);
            $this->paymentLogRepo->create([
                'to' => Statuses::VALIDATION_FAILED,
                'from' => $payment->status,
                'transaction_details' => $payment->transaction_details
            ]);
            $payment->status = Statuses::VALIDATION_FAILED;
            $payment->transaction_details = json_encode($request);
        }
        $payment->update();
        return $payment;
    }

    private function sslIpnHashValidation()
    {
        if (!(request()->has('verify_key') && request()->has('verify_sign'))) return false;

        $pre_define_key = explode(',', request('verify_key'));
        $new_data = [];
        if (empty($pre_define_key)) return false;

        foreach ($pre_define_key as $value) {
            if (request()->exists($value)) {
                $new_data[$value] = request($value);
            }
        }
        $new_data['store_passwd'] = md5($this->store->getStorePassword());
        ksort($new_data);
        $hash_string = "";
        foreach ($new_data as $key => $value) {
            $hash_string .= $key . '=' . ($value) . '&';
        }
        $hash_string = rtrim($hash_string, '&');
        return md5($hash_string) == request('verify_sign');
    }

    private function validateOrder()
    {
        try {
            $response = $this->validateFromSsl();
            Redis::set('ssl_' . Carbon::now()->timestamp, json_encode($response));
        } catch (TPProxyServerError $e) {
            $response = new stdClass();
            $response->status = "ERROR";
            $response->errorMessage = $e->getMessage();
            $response->code = $e->getCode();
            $response->file = $e->getFile();
            $response->line = $e->getLine();
            $response->request = request()->all();
            $response->tran_id = null;
        }
        return $response;
    }

    /**
     * @return mixed
     * @throws TPProxyServerError
     */
    private function validateFromSsl()
    {
        $url = $this->store->getOrderValidationUrl();
        $url .= "?val_id=" . request('val_id');
        $url = $this->addIdPasswordToUrl($url);
        return $this->tpClient->call((new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_GET));
    }

    /**
     * @param $bank_transaction_id
     * @param $amount
     * @return mixed
     * @throws TPProxyServerError
     */
    public function refund($bank_transaction_id, $amount)
    {
        $url = $this->store->getRefundUrl();
        $url .= "?refund_amount=" . urlencode($amount);
        $url .= "&refund_remarks=" . urlencode('Customer Refund');
        $url .= "&bank_tran_id=" . urlencode($bank_transaction_id);
        $url = $this->addIdPasswordToUrl($url);
        $url .= "&v=1&format=json";

        return $this->tpClient->call((new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_GET));
    }

    /**
     * @param $refund_ref_id
     * @return mixed
     * @throws TPProxyServerError
     */
    public function getRefundStatus($refund_ref_id)
    {
        $url = $this->store->getRefundUrl();
        $url .= "?refund_ref_id=$refund_ref_id";
        $url = $this->addIdPasswordToUrl($url);
        $url .= "&format=json";

        return $this->tpClient->call((new TPRequest())->setUrl($url)->setMethod(TPRequest::METHOD_GET));
    }

    private function addIdPasswordToUrl($url)
    {
        $url .= "&store_id=" . $this->store->getStoreId();
        $url .= "&store_passwd=" . $this->store->getStorePassword();
        return $url;
    }

    public function getMethodName()
    {
        return $this->isDonate ? self::NAME_DONATE : self::NAME;
    }
}
