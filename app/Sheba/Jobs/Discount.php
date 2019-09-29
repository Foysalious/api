<?php namespace Sheba\Jobs;

use App\Models\Job;

class Discount
{
    /**
     * RETURN FINAL DISCOUNT AMOUNT OF AN ORDER.
     *
     * @param Job $job
     * @return float|int
     */
    public function get(Job $job)
    {
        $job = !$job->isCalculated ? $job->calculate(true) : $job;

        $voucher = $job->partnerOrder->order->voucher;
        if(($job->discount_percentage && $job->discount_percentage != "0.00") || $voucher) {
            if($voucher) {
                $discount = $voucher->is_amount_percentage ? ($job->totalPrice * $voucher->amount / 100) : $voucher->amount;
                if ($voucher->cap) {
                    $discount = ($discount > $voucher->cap) ? $voucher->cap : $discount;
                }
                $discount = ($discount > $job->totalPrice) ?  $job->totalPrice : $discount;
            } else {
                $discount = $job->totalPrice * $job->discount_percentage / 100;
            }

        } else {
            $discount = $job->ownDiscount;
        }

        return $discount;
    }
}