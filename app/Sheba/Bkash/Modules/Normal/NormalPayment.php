<?php namespace Sheba\Bkash\Modules\Normal;


use App\Models\Payment;
use Sheba\Bkash\Modules\BkashPayment;

class NormalPayment extends BkashPayment
{

    public function getCreateBody(Payment $payment)
    {
        return json_encode(array(
            'amount' => $payment->payable->amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $payment->transaction_id,
        ));
    }
}