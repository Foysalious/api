<?php

namespace Sheba\Bkash\Modules\Normal\Methods\Payout;


use Sheba\Bkash\Modules\Normal\Methods\Payout\Responses\B2CPaymentResponse;
use Sheba\Bkash\Modules\Normal\NormalModule;

class NormalPayout extends NormalModule
{
    public function sendPayment($amount, $transaction_id, $receiver_bkash_no)
    {
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

    /**
     * @return array
     */
    private function getHeader()
    {
        $header = array(
            'Content-Type:application/json',
            'authorization:' . $this->getToken(),
            'x-app-key:' . $this->bkashAuth->appKey);
        return $header;
    }
}