<?php

namespace App\Http\Controllers;


use App\Models\Rate;
use App\Models\ReviewQuestionAnswer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Redis;

class RateController extends Controller
{
    public function index($customer, $job, Request $request)
    {
        try {
            $rates = Rate::where('type', 'review')->with(['questions' => function ($q) {
                $q->select('id', 'question', 'type')->with(['answers' => function ($q) {
                    $q->select('id', 'answer', 'asset', 'badge');
                }]);
            }])->select('id', 'name', 'icon', 'icon_off', 'value')->get();
            foreach ($rates as $rate) {
                $rate['asset'] = 'star';
                $rate['height'] = 30;
                foreach ($rate->questions as $question) {
                    $question['is_compliment'] = ($rate->value == 5) ? 1 : 0;
                    array_forget($question, 'pivot');
                    foreach ($question->answers as $answer) {
                        array_forget($answer, 'pivot');
                    }
                }
            }
            $rates = $rates->sortBy('value')->values()->all();
            Redis::set('customer-review-settings', json_encode($rates));
            return api_response($request, $rates, 200, ['rates' => $rates, 'rate_message' => 'Rate this job']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function store($customer, $resource, Request $request)
    {
        try {
            $job = $request->job;
            $review = $job->review;
            if ($review == null) return api_response($request, null, 403, ['message' => 'First you have to give a rating.']);
            if ($this->storeReviews($request, $review)) return api_response($request, 1, 200);
            else return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeCustomerReview($resource, Request $request)
    {
        try {
            $job = $request->job;
            $review = $job->customerReview;
            if ($review == null) return api_response($request, null, 403, ['message' => 'First you have to give a rating.']);
            if ($this->storeReviews($request, $review)) return api_response($request, 1, 200);
            else return api_response($request, null, 500);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function initializeReviewQuestionAnswer($review, $rate_id, $qa)
    {
        $review_answer = new ReviewQuestionAnswer();
        $review_answer->review_type = "App\\Models\\" . class_basename($review);
        $review_answer->review_id = $review->id;
        $review_answer->rate_id = (int)$rate_id;
        $review_answer->rate_question_id = $qa->question;
        return $review_answer;
    }

    private function storeReviews(Request $request, $review)
    {
        $data = json_decode(preg_replace("/\r|\n/", " ", $request->data));
        try {
            DB::transaction(function () use ($data, $review, $request) {
                $quesAns = $data->quesAns ?? [];
                $quesAnsText = $data->quesAnsText ?? [];
                $old_review_answer_ids = $review->rates->pluck('id')->toArray();
                if (count($quesAns) > 0) {
                    foreach ($quesAns as $qa) {
                        $answers = is_array($qa->answer) ? $qa->answer : [$qa->answer];
                        foreach ($answers as $answer) {
                            $review_answer = $this->initializeReviewQuestionAnswer($review, $request->rate, $qa);
                            $review_answer->rate_answer_id = $answer;
                            $review_answer->save();
                        }
                    }
                }
                if (count($quesAnsText) > 0) {
                    foreach ($quesAnsText as $qa) {
                        $review_answer = $this->initializeReviewQuestionAnswer($review, $request->rate, $qa);
                        $review_answer->rate_answer_text = $qa->answer;
                        $review_answer->save();
                    }
                }
                ReviewQuestionAnswer::whereIn('id', $old_review_answer_ids)->delete();
            });
        } catch (QueryException $e) {
            return null;
        }
        return true;
    }
}