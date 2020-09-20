<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class ReviewController extends Controller
{
    use ModificationFields;

    public function store($business, $order, $job, Request $request)
    {
        try {
            $this->validate($request, ['rating' => 'required']);
            $customer = $request->manager_member->customer;
            $business =  $request->business;
            $manager_member =  $request->manager_member;
            $job = $request->job;
            if ($job->status != 'Served') return api_response($request, null, 403, ['message' => 'Your Order hasn\'t been closed yet.']);
            $review = $job->review;
            $this->setModifier($customer);
            if ($review == null) {
                $review = new Review();
                $review->rating = $request->rating;
                $review->job_id = $job->id;
                $review->resource_id = $job->resource_id;
                $review->partner_id = $job->partnerOrder->partner_id;
                $review->category_id = $job->category_id;
                $review->customer_id = $customer->id;
                $this->withCreateModificationField($review);
                $review->save();
            }
            return api_response($request, $review, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}