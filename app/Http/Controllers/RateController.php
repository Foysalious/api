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
            }])->select('id', 'name', 'icon', 'value')->get();
            foreach ($rates as $rate) {
                array_add($rate, 'height', '30dp');
                foreach ($rate->questions as $question) {
                    array_forget($question, 'pivot');
                    foreach ($question->answers as $answer) {
                        array_forget($answer, 'pivot');
                    }
                }
            }
            return api_response($request, $rates, 200, ['rates' => $rates, 'rate_message']);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            $this->validate($request, ['question' => 'required|string', 'answer' => 'sometimes|required|string', 'answer_id' => 'sometimes|required|numeric']);
            $job = $request->job;
            $review = $job->review;
            if ($review == null) {
                return api_response($request, null, 403);
            }
            if ($review->rate != null) {
                return api_response($request, null, 403);
            }
            $review_answer = new ReviewQuestionAnswer();
            $review_answer->review_type = "App\\Models\\Review";
            $review_answer->review_id = $review->id;
            $review_answer->rate_question_id = $request->question;
            $review_answer->rate_answer_id = $request->answer_id;
            $review_answer->rate_answer_text = $request->answer;
            $review_answer->rate_id = $request->rate;
            $review_answer->save();
            return api_response($request, $review_answer, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}