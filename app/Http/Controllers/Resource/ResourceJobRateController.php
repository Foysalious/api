<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\CustomerReview;
use App\Models\Job;
use App\Models\Rate;
use App\Models\ReviewQuestionAnswer;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use DB;

class ResourceJobRateController extends Controller
{
    public function index($job, Request $request)
    {
        $rates = Rate::where('type', 'customer_review')->with(['questions' => function ($q) {
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
    }

    public function storeCustomerRating(Job $job, Request $request)
    {
        $this->validate($request, ['rating' => 'required|numeric']);
        if ($job->status != 'Served') {
            return api_response($request, null, 403);
        }
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $review = $job->customerReview;
        if ($review == null) {
            $review = new CustomerReview();
            $review->rating = $request->rating;
            $review->job_id = $job->id;
            $review->reviewable_id = $resource->id;
            $review->reviewable_type = "App\\Models\\Resource";
            $review->customer_id = $job->partnerOrder->order->customer_id;
            $review->created_by = $resource->id;
            $review->created_by_name = "Resource - " . $resource->profile->name;
            $review->save();
        }
        return api_response($request, $review, 200);
    }

    public function storeCustomerReview(Job $job, Request $request)
    {
        $review = $job->customerReview;
        if ($review == null) return api_response($request, null, 403, ['message' => 'First you have to give a rating.']);
        if ($this->storeReviews($request, $review)) return api_response($request, 1, 200);
        else return api_response($request, null, 500);
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
}