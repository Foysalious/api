<?php

namespace App\Http\Controllers;


use App\Models\Rate;
use App\Models\ReviewQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RateController extends Controller
{
    public function index($customer, $job, Request $request)
    {
        try {
            $rates = Rate::where('type', 'review')->with(['questions' => function ($q) {
                $q->select('id', 'question', 'type')->with(['answers' => function ($q) {
                    $q->select('id', 'answer', 'badge');
                }]);
            }])->select('id', 'name', 'icon', 'icon_off', 'value')->get();
            foreach ($rates as $rate) {
                array_add($rate, 'height', 30);
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

    public function store($customer, $resource, Request $request)
    {
        try {
            $job = $request->job;
            dd($customer, $resource);
            $review = $job->review;
            if ($review == null) {
                return api_response($request, null, 403);
            }
            if (count($review->rates) > 0) {
                return api_response($request, null, 403);
            }
            $data = json_decode($request->data);
            $quesAns = $data->quesAns;
            $quesAnsText = $data->quesAnsText;
            if (count($quesAns) > 0) {
                foreach ($quesAns as $qa) {
                    $review_answer = $this->initializeReviewQuestionAnswer($review, $request->rate, $qa);
                    $review_answer->rate_answer_id = $qa->answer;
                    $review_answer->save();
                }
            }
            if (count($quesAnsText) > 0) {
                foreach ($quesAnsText as $qa) {
                    $review_answer = $this->initializeReviewQuestionAnswer($review, $request->rate, $qa);
                    $review_answer->rate_answer_text = $qa->answer;
                    $review_answer->save();
                }
            }
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function storeCustomerReview($resource, Request $request)
    {
        try {
            $job = $request->job;
            $review = $job->customerReview;
            if ($review == null) {
                return api_response($request, null, 403);
            }
            if (count($review->rates) > 0) {
                return api_response($request, null, 403);
            }
            $data = json_decode($request->data);
            $quesAns = $data->quesAns;
            $quesAnsText = $data->quesAnsText;
            if (count($quesAns) > 0) {
                foreach ($quesAns as $qa) {
                    $review_answer = $this->initializeReviewQuestionAnswer($review, $request->rate, $qa);
                    $review_answer->rate_answer_id = $qa->answer;
                    $review_answer->save();
                }
            }
            if (count($quesAnsText) > 0) {
                foreach ($quesAnsText as $qa) {
                    $review_answer = $this->initializeReviewQuestionAnswer($review, $request->rate, $qa);
                    $review_answer->rate_answer_text = $qa->answer;
                    $review_answer->save();
                }
            }
            return api_response($request, 1, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function initializeReviewQuestionAnswer($review, $rate_id, $qa)
    {
        $review_answer = new ReviewQuestionAnswer();
        $review_answer->review_type = "App\\Models\\Review";
        $review_answer->review_id = $review->id;
        $review_answer->rate_id = (int)$rate_id;
        $review_answer->rate_question_id = $qa->question;
        return $review_answer;
    }
}