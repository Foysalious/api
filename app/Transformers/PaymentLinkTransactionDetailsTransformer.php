<?php

namespace App\Transformers;

use Carbon\Carbon;

class PaymentLinkTransactionDetailsTransformer
{
    public function transform($payment, $link)
    {
        return [
            'link_id' => $link['linkId'],
            'payment_id' => $payment->id,
            'link_type' => $link['type'],
            'user_id' => $link['userId'],
            'user_type' => $link['userType'],
            'link' => $link['link'],
            'link_identifier' => $link['linkIdentifier'],
            'reason' => $link['reason'],
            'is_active' => $link['isActive'],
            'is_default' => $link['isDefault'],
            'customer_name' => $payment->payable->getName(),
            'customer_number' => $payment->payable->getMobile(),
            'payment_code' => '#' . $payment->id,
            'amount' => $payment->payable->amount,
            'created_at' => Carbon::parse($payment->created_at)->format('Y-m-d h:i a'),

        ];
    }
}