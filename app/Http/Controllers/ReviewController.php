<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;

class ReviewController extends Controller {
    private $reviewRepository;

    public function __construct()
    {
        $this->reviewRepository = new ReviewRepository();
    }

    public function modifyReview($customer, Request $request)
    {
        $review = Review::where([
            ['job_id', $request->get('job_id')],
            ['customer_id', $customer],
        ])->first();
        //There is already a review or rating for this job
        if ($review != null)
        {
            if ($request->get('rating')!='')
            {
                $review->rating = $request->get('rating');
            }
            if ($request->get('review_title')!='')
            {
                $review->review_title = $request->get('review_title');
            }
            if ($request->get('review')!='')
            {
                $review->review = $request->get('review');
            }
            $review->update();
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
        else
        {
            if ($this->reviewRepository->customerCanGiveReview($customer, $request->get('job_id')))
            {
                $review = new Review();
                if ($request->get('rating')!='')
                {
                    $review->rating = $request->get('rating');
                }
                if ($request->get('review_title')!='')
                {
                    $review->review_title = $request->get('review_title');
                }
                if ($request->get('review')!='')
                {
                    $review->review = $request->get('review');
                }
                $review->job_id = $request->get('job_id');
                $review->partner_id = $request->get('partner_id');
                $review->service_id = $request->get('service_id');
                $review->customer_id = $customer;
                $review->save();
                return response()->json(['msg' => 'successful', 'code' => 200]);
            }
            else
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
        }
    }
}
