<?php


namespace App\Repositories;

use App\Models\Job;

class ReviewRepository
{

    public function customerCanGiveReview($customer, $job)
    {
        $job = Job::where([
            ['id', $job],
            ['status', 'Served']
        ])->first();
        if ($job == null) {
            return false;
        }
        if ($job->partner_order->order->customer_id == $customer) {
            return true;
        } else
            return false;
    }

    public function getReviewBreakdown($review)
    {
        $breakdown = array();
        $ratings = $review->groupBy('rating');
        foreach ($ratings as $key => $rating) {
            $breakdown[$key] = $rating->count();
        }
//        dd($breakdown);
        return $breakdown;
    }
}