<?php namespace Sheba\Bkash\Modules\Tokenized;

use App\Models\Payment;
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
//            'callbackURL' => config('sheba.api_url') . '/v2/bkash/tokenized/payment/validate',
            'callbackURL' => 'https://api.dev-sheba.xyz/',
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
        curl_close($url);
        echo $resultdata;

        $obj = json_decode($resultdata);

        dd($obj);
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
        curl_close($url);

        $curl = curl_init($this->bkashAuth->url . '/checkout/payment/execute');
        $this->setCurlOptions($curl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['paymentID' => json_decode($payment->transaction_details)->paymentID]));
        $result_data = curl_exec($curl);
        $result_data = json_decode($result_data);
        if (curl_errno($curl) > 0) {
            $error = new \InvalidArgumentException('Bkash execute API error.');
            $error->paymentId = $payment->transaction_id;
            throw  $error;
        };
        curl_close($curl);
        return $result_data;
    }
}