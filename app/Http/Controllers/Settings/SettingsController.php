<?php namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\Settings\Payment\PaymentSetting;

class SettingsController extends Controller
{
    public function getCustomerReviewSettings($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $customer->load(['partnerOrders' => function ($q) {
                $q->select('partner_orders.created_at', 'partner_orders.cancelled_at', 'partner_orders.id', 'order_id', 'closed_at', 'partner_orders.partner_id')
                    ->where([['cancelled_at', null], ['partner_orders.created_at', '>=', Carbon::today()->subDays(30)]])
                    ->with(['partner' => function ($q) {
                        $q->select('partners.id', 'partners.name');
                    }, 'jobs' => function ($q) {
                        $q->select('jobs.id', 'partner_order_id', 'resource_id', 'category_id', 'status')->with(['review' => function ($q) {
                            $q->select('reviews.id', 'reviews.job_id', 'rating');
                        }, 'category' => function ($q) {
                            $q->select('categories.id', 'categories.name');
                        }, 'resource' => function ($q) {
                            $q->select('resources.id', 'resources.profile_id')->with(['profile' => function ($q) {
                                $q->select('profiles.id', 'profiles.pro_pic', 'profiles.name');
                            }]);
                        }]);
                    }])->orderBy('id', 'desc')->take(1);
            }, 'customerReviews' => function ($q) {
                $q->select('customer_reviews.id', 'customer_reviews.customer_id', 'customer_reviews.rating');
            }]);
            $info = null;
            $partner_order = $customer->partnerOrders->first();
            $job = $partner_order ? $partner_order->getActiveJob() : null;

            if (!$this->canTakeReview($job, $partner_order)) {
                $job = null;
            }

            if ($job && $partner_order->closed_at != null && $job->review == null) {
                $info['id'] = $job->id;
                $info['resource_name'] = trim($job->resource ? $job->resource->profile->name : null);
                $info['resource_picture'] = $job->resource ? $job->resource->profile->pro_pic : null;
                $info['partner_name'] = trim($partner_order->partner->name);
                $info['category_name'] = trim($job->category->name);
            }
            $settings = Redis::get('customer-review-settings');
            $settings = $settings ? json_decode($settings) : null;
            $rating = $customer->customerReviews->avg('rating') <= 5 ? $customer->customerReviews->avg('rating') : 5;
            return api_response($request, $info, 200, ['job' => $info, 'customer' => ['rating' => round($rating, 2)], 'settings' => $settings]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    protected function canTakeReview($job, $partner_order)
    {
        if (!$job) return false;
        $review = $job->review;

        if (!is_null($review) && $review->rating > 0) {
            return false;
        } else if ($partner_order->closed_at) {
            $closed_date = Carbon::parse($partner_order->closed_at);
            $now = Carbon::now();
            $difference = $closed_date->diffInDays($now);

            return $difference < constants('CUSTOMER_REVIEW_OPEN_DAY_LIMIT');
        } else {
            return false;
        }
    }

    public function getCustomerSettings($customer, Request $request)
    {
        try {
            /** @var Customer $customer */
            $customer = $request->customer;
            $reviews = $customer->reviews()->where('rating', '=', 5);
            $settings = [
                'credit' => $customer->shebaCredit(),
                'rating' => round($customer->customerReviews->avg('rating'), 2),
                'payments' => [
                    'is_bkash_saved' => $customer->profile->bkash_agreement_id ? 1 : 0
                ],
                'pending_order' => $customer->partnerOrders()->where('closed_and_paid_at', null)->where('cancelled_at', null)->whereHas('jobs', function($q){
                    $q->where('status', '<>', 'Cancelled');
                })->count(),
                'has_rated_customer_app' => ($customer->has_rated_customer_app == 1) ? 1 : (($reviews->count() >= 3) ? 0 : 1)
            ];
            return api_response($request, $settings, 200, ['settings' => $settings]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function giveRating($customer, Request $request)
    {
        try {
            /** @var Customer $customer */
            $customer = $request->customer;

            #$data['has_rated_customer_app'] = (int)$request->has_rated_customer_app;
            $data['has_rated_customer_app'] = 1;
            $customer->update($data);
            return api_response($request,null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function addPayment($customer, Request $request, PaymentSetting $paymentSetting)
    {
        try {
            //Payment Id is actually gateway transaction id
            $this->validate($request, [
                'payment_name' => 'sometimes|required|in:bkash',
                'order_id' => 'numeric',
                'order_type' => 'string',
                'payment_id' => 'string'
            ]);
            /** @var Customer $customer */
            $profile = $request->customer->profile;
            if ($profile->bkash_agreement_id) return api_response($request, null, 403, ['message' => "$request->payment is already saved"]);
            $response = $paymentSetting->setMethod($request->payment_name)->init($profile);
            $key = 'order_' . $response->transactionId;
            Redis::set($key, json_encode(['order_id' => (int)$request->order_id, 'order_type' => $request->order_type, 'gateway_transaction_id' => $request->payment_id]));
            Redis::expire($key, 60 * 60);
            return api_response($request, $response, 200, ['data' => array(
                'redirect_url' => $response->redirectUrl,
                'success_url' => $this->getSuccessUrl((int)$request->order_id, $request->order_type),
            )]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSuccessUrl($order_id, $order_type)
    {
        $front_url = config('sheba.front_url');
        if ($order_type == 'partner_order') {
            return $front_url . '/orders/' . (PartnerOrder::find($order_id))->jobs()->where('status', '<>', constants('JOB_STATUSES')['Cancelled'])->first()->id;
        } elseif ($order_type == 'subscription_order') {
            return $front_url . '/subscription-orders/' . $order_id;
        } else {
            return $front_url . '/profile/me';
        }
    }
}
