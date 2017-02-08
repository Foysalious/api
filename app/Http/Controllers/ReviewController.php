<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Job;
use App\Models\Review;
use App\Repositories\ReviewRepository;
use Illuminate\Http\Request;
use Gate;

class ReviewController extends Controller
{
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
//        return $review;
        //There is already a review or rating for this job
        if ($review != null) {
            if ($request->input('rating') != '') {
                $review->rating = $request->input('rating');
            }
            if ($request->input('review_title') != '') {
                $review->review_title = $request->input('review_title');
            }
            if ($request->input('review') != '') {
                $review->review = $request->input('review');
            }
            $review->update();
            return response()->json(['msg' => 'successful', 'code' => 200]);
        } else {
            if ($this->reviewRepository->customerCanGiveReview($customer, $request->input('job_id'))) {
                $review = new Review();
                if ($request->input('rating') != '') {
                    $review->rating = $request->input('rating');
                }
                if ($request->input('review_title') != '') {
                    $review->review_title = $request->input('review_title');
                }
                if ($request->input('review') != '') {
                    $review->review = $request->input('review');
                }
                $review->job_id = $request->input('job_id');
                $review->partner_id = $request->input('partner_id');
                $review->service_id = $request->input('service_id');
                $review->customer_id = $customer;
                $review->save();
                return response()->json(['msg' => 'successful', 'code' => 200]);
            } else
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
        }
    }

    public function giveRatingFromEmail(Request $request)
    {
        $this->validate($request, [
            'rating' => 'required|numeric|between:1,5',
            'j' => 'required|numeric',
            'c' => 'required|numeric',
            'token' => 'required|string',
        ]);
        if ($request->input('rating') > 5 || $request->input('rating') < 1) {
            return response()->json(['msg' => 'I see what you did there ;)', 'code' => 409]);
        }
        $customer = Customer::where('remember_token', $request->input('token'))->first();

        //customer is valid
        if ($customer && $customer->id == $request->input('c')) {
            $job = Job::find($request->input('j'));
            //customer can give review to this job
            if ($this->reviewRepository->customerCanGiveReview($customer->id, $request->input('j'))) {
                //if review isn't given yet
                if ($job->review == null) {
                    $review = new Review();
                    $review->rating = $request->input('rating');
                    $review->job_id = $request->input('j');
                    $review->partner_id = $job->partner_order->partner_id;
                    $review->service_id = $job->service_id;
//                    $review->resource_id = $job->resource_id;
                    $review->customer_id = $customer->id;
                    if ($review->save()) {
                        return response()->json(['msg' => 'successful', 'code' => 200]);
                    } else {
                        return response()->json(['msg' => 'error', 'code' => 500]);
                    }
                } //update the review
                else {
                    $review = $job->review;
                    $review->rating = $request->input('rating');
                    if ($review->update()) {
                        return response()->json(['msg' => 'successful', 'code' => 200]);
                    }
                }
            }
        }
        return response()->json(['msg' => 'unauthorized', 'code' => 409]);
    }

}
