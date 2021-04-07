<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;

class ReviewController extends Controller
{
    use ModificationFields;

    public function store($business, $partner_order, Request $request)
    {
        try {
            $this->validate($request, ['rating' => 'required']);
             $customer = $request->manager_member->customer;
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
            $rate = Rate::where([['type', 'review_from_business'], ['value', $request->rating]])->with(['questions' => function ($q) {
                $q->select('id', 'question', 'type')->with(['answers' => function ($q) {
                    $q->select('id', 'answer', 'asset', 'badge');
                }]);
            }])->select('id', 'name', 'icon', 'icon_off', 'value')->first();

            foreach ($rate->questions as $question) {
                $question['is_compliment'] = ($rate->value == 5) ? 1 : 0;
                array_forget($question, 'pivot');
                foreach ($question->answers as $answer) {
                    array_forget($answer, 'pivot');
                }
            }
            return api_response($request, $rate, 200, ['rate' => $rate]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}