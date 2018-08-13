<?php

namespace App\Http\Controllers\Settings;


use App\Http\Controllers\Controller;
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
                $q->select('partner_orders.id', 'order_id', 'closed_and_paid_at', 'partner_orders.partner_id')
                    ->where([['closed_and_paid_at', '<>', null], ['closed_at', '>=', Carbon::today()->subDays(600)]])
                    ->whereHas('jobs', function ($q) {
                        $q->has('review', 0);
                    })->with(['partner' => function ($q) {
                        $q->select('partners.id', 'partners.name');
                    }, 'jobs' => function ($q) {
                        $q->select('jobs.id', 'partner_order_id', 'resource_id', 'category_id')->with(['category' => function ($q) {
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
            if ($customer->partnerOrders) {
                $job = $customer->partnerOrders->first()->jobs->first();
                $info['id'] = $job->id;
                $info['resource_name'] = $job->resource->profile->name;
                $info['resource_picture'] = $job->resource->profile->pro_pic;
                $info['partner_name'] = $customer->partnerOrders->first()->partner->name;
                $info['category_name'] = $job->category->name;
            }
            $settings = Redis::get('customer-review-settings');
            $settings = $settings ? json_decode($settings) : null;
            return api_response($request, $job, 200,
                ['job' => $info, 'customer' => ['rating' => $customer->customerReviews->sum('rating')], 'settings' => $settings]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}