<?php

namespace App\Http\Controllers\Settings;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Redis;

class SettingsController extends Controller
{
    public function getCustomerReviewSettings($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $customer->load(['partnerOrders' => function ($q) {
                $q->select('partner_orders.created_at', 'partner_orders.cancelled_at', 'partner_orders.id', 'order_id', 'closed_at', 'partner_orders.partner_id')->where([['cancelled_at', null], ['partner_orders.created_at', '>=', Carbon::today()->subDays(30)]])->with(['partner' => function ($q) {
                    $q->select('partners.id', 'partners.name');
                }, 'jobs' => function ($q) {
                    $q->select('jobs.id', 'partner_order_id', 'resource_id', 'category_id')->with(['review', 'category' => function ($q) {
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
            $job = $partner_order ? $partner_order->jobs->first() : null;

            if(!$this->canTakeReview($job)) {
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
            return api_response($request, $info, 200, ['job' => $info, 'customer' => ['rating' => round($customer->customerReviews->avg('rating'), 2)], 'settings' => $settings]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    protected function canTakeReview($job)
    {
        $review = $job->review;

        if(!is_null($review) && $review->rating > 0) {
            return false;
        } else if($job->partnerOrder->closed_at) {
            $closed_date = Carbon::parse($job->partnerOrder->closed_at);
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
            $settings = array(
                'credit' => $customer->shebaCredit(),
                'rating' => round($customer->customerReviews->avg('rating'), 2),
                'pending_order' => $customer->partnerOrders->where('closed_and_paid_at', null)->where('cancelled_at', null)->count()
            );
            return api_response($request, $settings, 200, ['settings' => $settings]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}