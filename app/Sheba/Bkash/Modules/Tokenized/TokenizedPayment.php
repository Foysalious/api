<?php namespace Sheba\Bkash\Modules\Tokenized;

use App\Models\Payment;
use http\Exception\InvalidArgumentException;
use Sheba\Bkash\Modules\BkashPayment;

class TokenizedPayment extends BkashPayment
{
    public function getCreateBody(Payment $payment)
    {
        return json_encode(array(
            'amount' => (string)$payment->payable->amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => (string)$payment->transaction_id,
            'agreementID' => $payment->payable->user->getAgreementId(),
            'callbackURL' => config('sheba.api_url') . '/v2/bkash/tokenized/payment/validate'
        ));
    }

    public function create(Payment $payment)
    {
        $requestbody = array(
            'agreementID' => $this->bkashAuth->getTokenizedId(),
            'mode' => '0001',
            'intent' => 'sale',
            'callbackURL' => config('sheba.api_url') . '/v2/bkash/tokenized/payment/validate',
            'amount' => $payment->payable->amount,
            'currency' => 'BDT',
            'merchantInvoiceNumber' => $payment->transaction_id
        );

        $url = curl_init($this->bkashAuth->url . '/checkout/create');

        $requestbodyJson = json_encode($requestbody);
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $this->getToken(),
            'X-APP-Key:' . $this->bkashAuth->getAppKey()
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        if (curl_errno($url) > 0) throw new InvalidArgumentException("Payment couldn't be processed");
        curl_close($url);
        $obj = json_decode($resultdata);
        return $obj;
    }

    public function getToken()
    {
        return (new TokenizedToken())->setBkashAuth($this->bkashAuth)->get();
    }


    public function execute(Payment $payment)
    {
        $request_body = ['paymentID' => $payment->gateway_transaction_id];
        $url = curl_init($this->bkashAuth->url . '/checkout/execute');
        $request_body_json = json_encode($request_body);
        curl_setopt($url, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_body_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $resultdata = curl_exec($url);
        if (curl_errno($url) > 0) throw new InvalidArgumentException("Payment couldn't be processed");
        curl_close($url);
        $obj = json_decode($resultdata);
        return $obj;
    }
}