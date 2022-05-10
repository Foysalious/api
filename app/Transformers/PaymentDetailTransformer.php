<?php namespace App\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PaymentDetailTransformer extends TransformerAbstract
{
    private function mutateStatus($status)
    {
        if ($status == "initiated" || $status == "validated") return 'processed';
        if ($status == "initiation_failed" || $status == "validation_failed" || $status == "failed" || $status == "cancelled") return 'failed';
        if ($status == "completed") return 'completed';
        return 'processed';
    }

    public function transform($payment, $payment_detail, $payment_link_payment_details)
    {
        return [
            'customer_name' => $payment->payable->getName(),
            'customer_number' => $payment->payable->getMobile(),
            'payment_type' => $payment_detail->readableMethod,
            'id' => $payment->id,
            'payment_code' => '#' . $payment->id,
            'payment_status' => $this->mutateStatus($payment->status),
            'amount' => $payment->payable->amount,
            'description' => $payment->payable->description,
            'created_at' => Carbon::parse($payment->created_at)->format('Y-m-d h:i a'),
            'link' => $payment_link_payment_details['link'],
            'link_code' => '#' . $payment_link_payment_details['linkId'],
            'purpose' => $payment_link_payment_details['reason'],
            'status' => $payment_link_payment_details['isActive'] == 1 ? 'active' : 'inactive',
            'link_id' => $payment_link_payment_details['linkId'],
            'link_type' => $payment_link_payment_details['type'],
            'is_default' => $payment_link_payment_details['isDefault']
        ];
    }
}
