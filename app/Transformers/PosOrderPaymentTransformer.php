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
            'method' => $payment->method,
            'date' => $payment->created_at->format('Y-m-d h:i A')
        ];
    }
}