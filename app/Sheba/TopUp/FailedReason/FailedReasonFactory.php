<?php namespace Sheba\TopUp\FailedReason;

use App\Models\TopUpOrder;

class FailedReasonFactory
{
    public static function make(TopUpOrder $top_up_order)
    {
        $transaction = json_decode($top_up_order->transaction_details);
        if (property_exists($transaction, 'vr_guid')) {
            return new SslFailedReason();
        } else {
            return new PretupsFailedReason();
        }
    }
}