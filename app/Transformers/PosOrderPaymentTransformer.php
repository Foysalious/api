<?php namespace App\Transformers;

use App\Models\PosOrderPayment;
use League\Fractal\TransformerAbstract;

class PosOrderPaymentTransformer extends TransformerAbstract
{
    public function transform(PosOrderPayment $payment)
    {
        return [
            'amount' => $payment->amount,
            'transaction_type' => $payment->transaction_type,
            'method' => empty($payment->emi_month) ? $payment->method : 'emi',
            'date' => $payment->created_at->format('Y-m-d h:i A')
        ];
    }
}