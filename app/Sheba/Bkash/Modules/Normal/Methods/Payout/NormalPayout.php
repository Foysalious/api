<?php namespace Sheba\Bkash\Modules\Normal\Methods\Payout;

use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Bkash\Modules\Normal\Methods\Payout\Responses\B2CPaymentResponse;
use Sheba\Bkash\Modules\Normal\Methods\Payout\Responses\IntraAccountTransferResponse;
use Sheba\Bkash\Modules\Normal\NormalModule;

class NormalPayout extends NormalModule
{
    public function setBkashAuth()
    {
        $this->bkashAuth = new BkashAuth();
        $this->bkashAuth->setKey(config('bkash.payout.app_key'))
            ->setSecret(config('bkash.payout.app_secret'))
            ->setUsername(config('bkash.payout.username'))
            ->setPassword(config('bkash.payout.password'))->setUrl(config('bkash.payout.url'));
    }

    protected function setToken()
    {
        $this->token = new PayoutToken();
    }

    public function getToken()
    {
        return $this->token->setBkashAuth($this->bkashAuth)->get();
    }

    /**
     * @param $amount
     * @param $transaction_id
     * @param $receiver_bkash_no
     * @return bool|B2CPaymentResponse
     */
    public function sendPayment($amount, $transaction_id, $receiver_bkash_no)
    {
        if (!$this->intraAccountTransfer($amount)) return false;
        $payment_body = json_encode(array(
            'amount' => (double)$amount,
            'currency' => 'BDT',
            'merchantInvoiceNumber' => $transaction_id,
            'receiverMSISDN' => $receiver_bkash_no
        ));
        $curl = curl_init($this->bkashAuth->url . '/checkout/payment/b2cPayment');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payment_body);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('Bkash create API error.');
        curl_close($curl);
        return (new B2CPaymentResponse())->setResponse(json_decode($result_data));
    }

    private function intraAccountTransfer($amount)
    {
        $payment_body = json_encode(array(
            'amount' => (double)$amount,
            'currency' => 'BDT',
            'transferType' => 'Collection2Disbursement',
        ));
        $curl = curl_init($this->bkashAuth->url . '/checkout/payment/intraAccountTransfer');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payment_body);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('Bkash create API error.');
        curl_close($curl);
        $response = new IntraAccountTransferResponse();
        $response->setResponse(json_decode($result_data));
        return $response->hasSuccess();
    }

    /**
     * @return array
     */
    private function getHeader()
    {
        return [
            'Content-Type:application/json',
            'authorization:' . $this->getToken(),
            'x-app-key:' . $this->bkashAuth->appKey
        ];
    }
}