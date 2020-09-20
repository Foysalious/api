<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\ReviewQuestionAnswer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;

class RateController extends Controller
{
    public function store($business, $order, Request $request)
    {
        try {
            $job = $request->job;
            $review = $job->review;
            if ($review == null) return api_response($request, null, 403, ['message' => 'First you have to give a rating.']);
            if ($this->storeReviews($request, $review)) return api_response($request, 1, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function storeReviews(Request $request, $review)
    {
        $data = json_decode(preg_replace("/\r|\n/", " ", $request->data));
        try {
            DB::transaction(function () use ($data, $review, $request) {
                $quesAns = $data->quesAns;
                $quesAnsText = $data->quesAnsText;
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

    private function initializeReviewQuestionAnswer($review, $rate_id, $qa)
    {
        $review_answer = new ReviewQuestionAnswer();
        $review_answer->review_type = "App\\Models\\" . class_basename($review);
        $review_answer->review_id = $review->id;
        $review_answer->rate_id = (int)$rate_id;
        $review_answer->rate_question_id = $qa->question;
        return $review_answer;
    }
}