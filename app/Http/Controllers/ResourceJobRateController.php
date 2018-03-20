<?php

namespace App\Http\Controllers;


use App\Models\Rate;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResourceJobRateController extends Controller
{
    public function index($resource, $job, Request $request)
    {
        try {
            $rates = Rate::where('type', 'customer_review')->with(['questions' => function ($q) {
                $q->select('id', 'question', 'type')->with(['answers' => function ($q) {
                    $q->select('id', 'answer', 'badge');
                }]);
            }])->select('id', 'name', 'icon', 'value')->get();
            foreach ($rates as $rate) {
                array_add($rate, 'height', 30);
                array_add($rate, 'icon_not_selected', 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/sheba_xyz/rate2.png');
                foreach ($rate->questions as $question) {
                    array_forget($question, 'pivot');
                    foreach ($question->answers as $answer) {
                        array_forget($answer, 'pivot');
                    }
                }
            }
            $rates = $rates->sortBy('value')->values()->all();
            return api_response($request, $rates, 200, ['rates' => $rates, 'rate_message' => 'Rate this job']);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function review($resource, $job, Request $request)
    {
        try {
            $rates = Rate::where('type', 'customer_review')->get();
            $max = $rates->max('value');
            $min = $rates->min('value');
            $this->validate($request, ['rating' => 'required|numeric|between:' . $min . ',' . $max]);
            $job = $request->job;
            if ($job->status != 'Served') {
                return api_response($request, null, 403);
            }
            $review = $job->review;
            if ($review == null) {
                $review = new Review();
                $review->rating = $request->rating;
                $review->job_id = $job->id;
                $review->resource_id = $resource;
                $review->partner_id = $job->partner_order->partner_id;
                $review->category_id = $job->category_id;
                $review->customer_id = $job->order->customer_id;
                $review->save();

            }
            return api_response($request, $review, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}