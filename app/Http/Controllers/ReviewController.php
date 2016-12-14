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
            ['job_id', $request->input('job_id')],
            ['customer_id', $customer],
        ])->first();
        //There is already a review or rating for this job
        if ($review != null)
        {
            if ($request->input('rating')!='')
            {
                $review->rating = $request->input('rating');
            }
            if ($request->input('review_title')!='')
            {
                $review->review_title = $request->input('review_title');
            }
            if ($request->input('review')!='')
            {
                $review->review = $request->input('review');
            }
            $review->update();
            return response()->json(['msg' => 'successful', 'code' => 200]);
        }
        else
        {
            if ($this->reviewRepository->customerCanGiveReview($customer, $request->input('job_id')))
            {
                $review = new Review();
                if ($request->input('rating')!='')
                {
                    $review->rating = $request->input('rating');
                }
                if ($request->input('review_title')!='')
                {
                    $review->review_title = $request->input('review_title');
                }
                if ($request->input('review')!='')
                {
                    $review->review = $request->input('review');
                }
                $review->job_id = $request->input('job_id');
                $review->partner_id = $request->input('partner_id');
                $review->service_id = $request->input('service_id');
                $review->customer_id = $customer;
                $review->save();
                return response()->json(['msg' => 'successful', 'code' => 200]);
            }
            else
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
        }
    }
}
