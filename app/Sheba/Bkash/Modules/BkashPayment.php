<?php namespace Sheba\Bkash\Modules;


use App\Models\Payment;
use Sheba\Bkash\Modules\Tokenized\TokenizedToken;

abstract class BkashPayment
{
    /** @var $bkashAuth BkashAuth */
    protected $bkashAuth;

    public function setBkashAuth(BkashAuth $bkashAuth)
    {
        $this->bkashAuth = $bkashAuth;
        return $this;
    }

    abstract public function getCreateBody(Payment $payment);

    public function create(Payment $payment)
    {
        $curl = curl_init($this->bkashAuth->url . '/checkout/payment/create');
        $this->setCurlOptions($curl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getCreateBody($payment));
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('Bkash create API error.');
        curl_close($curl);
        dd(json_decode($result_data));
        return json_decode($result_data);
    }

    public function execute(Payment $payment)
    {
        $curl = curl_init($this->bkashAuth->url . '/checkout/payment/execute/' . json_decode($payment->transaction_details)->paymentID);
        $this->setCurlOptions($curl);
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

    private function getHeader()
    {
        return array(
            'Content-Type:application/json',
            'authorization:' . $this->getToken(),
            'x-app-key:' . $this->bkashAuth->appKey);
    }

    private function getToken()
    {
        return (new TokenizedToken())->setBkashAuth($this->bkashAuth)->get();
    }

    private function setCurlOptions($curl)
    {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
    }
}