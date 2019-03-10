<?php namespace Sheba\Bkash\Modules\Tokenized;

use App\Models\Payment;
use Sheba\Bkash\Modules\BkashPayment;

class TokenizedPayment extends BkashPayment
{
    public function getCreateBody(Payment $payment)
    {
        return json_encode(array(
            'amount' => $payment->payable->amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $payment->transaction_id,
            'agreementID' => $payment->payable->user->getAgreementId(),
            'callbackURL' => config('sheba.api_url') . '/v2/bkash/tokenized/payment/validate'
        ));
    }

}