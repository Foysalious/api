<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\Reward\ActionRewardDispatcher;

class ReviewController extends Controller
{
    use ModificationFields;

    public function store($customer, $job, Request $request, ActionRewardDispatcher $dispatcher)
    {
        try {
            $this->validate($request, ['rating' => 'required']);
            $job = $request->job;
            if ($job->status != 'Served') return api_response($request, null, 403, ['message' => 'Your Order hasn\'t been closed yet.']);
            $review = $job->review;
            $customer = $request->customer;
            $this->setModifier($customer);
            if ($review == null) {
                $review = new Review();
                $review->rating = $request->rating;
                $review->job_id = $job->id;
                $review->resource_id = $job->resource_id;
                $review->partner_id = $job->partner_order->partner_id;
                $review->category_id = $job->category_id;
                $review->customer_id = $customer->id;
                $this->withCreateModificationField($review);
                $review->save();
                $dispatcher->run('rating', $job->partner_order->partner, $review);
            } else {
                $review->rating = $request->rating;
                $this->withUpdateModificationField($review);
                $review->update();
                $review->rates()->delete();
            }
            return api_response($request, $review, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
