<?php


namespace App\Repositories;

use App\Models\Customer;
use App\Models\Job;
use App\Models\Review;
use Illuminate\Http\Request;

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
            return $job;
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
        return $breakdown;
    }

    /**
     * return reviews for an object i.e. service,partner
     * @param $object
     * @return mixed
     */
    public function getGeneralReviewInformation($object)
    {
        $review = $object->reviews->filter(function ($item) {
            return $item->review != '';
        })->count();
        array_add($object, 'review_count', $review);
        array_add($object, 'rating_count', $object->reviews->count());
        $rating = $object->reviews->avg('rating');
        if ($rating == null) {
            $rating = 5;
        }
        array_add($object, 'rating', round($rating, 1));
        return $object;
    }

    public function save(Job $job, Request $request)
    {
        $review = new Review();
        $review->rating = $request->rating;
        $review->review_title = $request->review_title;
        $review->review = $request->review;
        $review->job_id = $job->id;
        $review->resource_id = $job->resource_id;
        $review->partner_id = $job->partner_order->partner_itd;
        $review->service_id = $job->service_id;
        $review->customer_id = $job->partner_order->order->customer_id;
        $review->save();
        return $review;
    }

    public function update(Review $review, Request $request)
    {
        $review->rating = $request->rating;
        $review->review_title = $request->review_title;
        $review->review = $request->review;
        $review->update();
        return $review;
    }

}