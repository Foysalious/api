<?php


namespace App\Repositories;

use App\Models\Job;

class ReviewRepository {

    public function customerCanGiveReview($customer, $job)
    {
        $job = Job::where([
            ['id', $job],
            ['status', 'Closed']
        ])->first();
        if ($job->partner_order->order->customer_id == $customer)
        {
            return true;
        }
        else
            return false;
    }
}