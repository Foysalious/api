<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class CustomerNotificationController extends Controller
{

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            /*dd($customer->orders()->with(['jobs', function($query){
                $query->where('status', '<>', ['Cancelled']);
            }]));*/
            $orders = [];
            $order_1 = [
                'job_id' => 1,
                'category_id' => 1,
                'name' => 'Pure Milk',
                'app_thumb' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1549878615_dairy_milk_and_ghee.png',
                'order_statuses' => [
                    [
                        'text' => 'Order served successfully',
                        'icon' => '',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order is in process',
                        'icon' => '',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order has been accepted',
                        'icon' => '',
                        'date' => '30 Jul at 12:47 PM',
                    ]
                ]
            ];
            $order_2 = [
                'job_id' => 1,
                'category_id' => 1,
                'name' => 'Pure Milk',
                'app_thumb' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1549878615_dairy_milk_and_ghee.png',
                'order_statuses' => [
                    [
                        'text' => 'Order served successfully',
                        'icon' => '',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order is in process',
                        'icon' => '',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order has been accepted',
                        'icon' => '',
                        'date' => '30 Jul at 12:47 PM',
                    ]
                ]
            ];


            array_push($orders, $order_1, $order_2);
            return api_response($request, null, 200, ['orders' => $orders]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function update($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $notifications = collect($request->notification);
            $notifications = $notifications->map(function ($notification) {
                return (int)$notification;
            })->toArray();
            $notifications = $customer->notifications->whereIn('id', $notifications);
            if (count($notifications) == 0) {
                return api_response($request, null, 404);
            }
            foreach ($notifications as $notification) {
                $notification->timestamps = false;
                $notification->is_seen = 1;
                $notification->update();
            }
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}