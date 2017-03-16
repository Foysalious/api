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
    /**
     * return reviews for an object i.e. service,partner
     * @param $object
     * @return mixed
     */
    public function getReviews($object)
    {
        // review count of this
        $review = $object->reviews()->where('review', '<>', '')->count('review');
        array_add($object, 'review_count', $review);
        //rating count of this
        $total_rating = $object->reviews()->where('rating', '<>', '')->count('rating');
        array_add($object, 'rating_count', $total_rating);
        //avg rating of this
        $rating = $object->reviews()->avg('rating');
        array_add($object, 'rating', round($rating, 1));
        return $object;
    }

}