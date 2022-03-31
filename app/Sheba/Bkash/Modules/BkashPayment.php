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

    abstract public function create(Payment $payment);

    abstract public function execute(Payment $payment);

    abstract public function getToken();

    protected function getHeader()
    {
        return array(
            'Content-Type:application/json',
            'authorization:' . $this->getToken(),
            'x-app-key:' . $this->bkashAuth->appKey);
    }

    protected function setCurlOptions($curl)
    {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
    }
}