<?php

namespace Sheba\Payment\Methods\Bkash;

use App\Models\Payable;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use Sheba\ModificationFields;
use Redis;
use Sheba\Payment\Methods\Bkash\Response\ExecuteResponse;
use Sheba\Payment\Methods\PaymentMethod;
use Sheba\RequestIdentification;
use DB;

class Bkash extends PaymentMethod
{
    use ModificationFields;
    private $appKey;
    private $appSecret;
    private $username;
    private $password;
    private $url;
    CONST NAME = 'bkash';

    public function __construct()
    {
        parent::__construct();
        $this->appKey = config('bkash.app_key');
        $this->appSecret = config('bkash.app_secret');
        $this->username = config('bkash.username');
        $this->password = config('bkash.password');
        $this->url = config('bkash.url');
    }

    public function init(Payable $payable): Payment
    {
        $invoice = "SHEBA_BKASH_" . strtoupper($payable->readable_type) . '_' . $payable->type_id . '_' . Carbon::now()->timestamp;
        $payment = new Payment();
        DB::transaction(function () use ($payment, $payable, $invoice) {
            $payment->payable_id = $payable->id;
            $payment->transaction_id = $invoice;
            $payment->status = 'initiated';
            $payment->valid_till = Carbon::tomorrow();
            $this->setModifier($payable->user);
            $payment->fill((new RequestIdentification())->get());
            $this->withCreateModificationField($payment);
            $payment->save();
            $payment_details = new PaymentDetail();
            $payment_details->payment_id = $payment->id;
            $payment_details->method = self::NAME;
            $payment_details->amount = $payable->amount;
            $payment_details->save();
        });
        $data = $this->create($payment);
        $payment->transaction_id = $data->merchantInvoiceNumber;
        $payment->transaction_details = json_encode($data);
        $payment->redirect_url = config('sheba.front_url') . '/bkash?paymentID=' . $data->merchantInvoiceNumber;
        $payment->update();
        return $payment;
    }

    public function validate(Payment $payment)
    {
        $execute_response = new ExecuteResponse();
        $execute_response->setPayment($payment);
        $execute_response->setResponse($this->execute($payment));
        $this->paymentRepository->setPayment($payment);
        if ($execute_response->hasSuccess()) {
            $success = $execute_response->getSuccess();
            $this->paymentRepository->changeStatus(['to' => 'validated', 'from' => $payment->status,
                'transaction_details' => $payment->transaction_details]);
            $payment->status = 'validated';
            $payment->transaction_details = json_encode($success->details);
        } else {
            $error = $execute_response->getError();
            $this->paymentRepository->changeStatus(['to' => 'validation_failed', 'from' => $payment->status,
                'transaction_details' => $payment->transaction_details]);
            $payment->status = 'validation_failed';
            $payment->transaction_details = json_encode($error->details);
        }
        $payment->update();
        return $payment;
    }

    private function create(Payment $payment)
    {
        $token = Redis::get('BKASH_TOKEN');
        $token = $token ? $token : $this->grantToken();
        $intent = 'sale';
        $create_pay_body = json_encode(array(
            'amount' => $payment->payable->amount,
            'currency' => 'BDT',
            'intent' => $intent,
            'merchantInvoiceNumber' => $payment->transaction_id
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
        curl_setopt($url, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($url);
        if (curl_errno($url) > 0) throw new \InvalidArgumentException('Bkash create API error.');
        curl_close($url);
        return json_decode($result_data);
    }

    private function grantToken()
    {
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
        curl_setopt($url, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($url);
        if (curl_errno($url) > 0) throw new \InvalidArgumentException('Bkash grant token API error.');
        curl_close($url);
        $data = json_decode($result_data, true);
        $token = $data['id_token'];
        Redis::set('BKASH_TOKEN', $token);
        Redis::expire('BKASH_TOKEN', (int)$data['expires_in'] - 100);
        return $token;
    }

    private function execute(Payment $payment)
    {
        $token = Redis::get('BKASH_TOKEN');
        $token = $token ? $token : $this->grantToken();
        $url = curl_init($this->url . '/checkout/payment/execute/' . json_decode($payment->transaction_details)->paymentID);
        $header = array(
            'authorization:' . $token,
            'x-app-key:' . $this->appKey);
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($url);
        $result_data = json_decode($result_data);
        if (curl_errno($url) > 0) {
            $error = new \InvalidArgumentException('Bkash execute API error.');
            $error->paymentId = $payment->transaction_id;
            throw  $error;
        };
        curl_close($url);
        return $result_data;
    }
}