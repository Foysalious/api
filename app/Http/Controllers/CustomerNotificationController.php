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
            $notifications = [];
            $notification_1 = [
                'type_id' => 154919,
                'type' => 'Job',
                'text' => 'Pure Milk',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1549878615_dairy_milk_and_ghee.png',
                'statuses' => [
                    [
                        'text' => 'Order served successfully',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order is in process',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order has been accepted',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/accepted.png',
                        'date' => '30 Jul at 12:47 PM',
                    ]
                ]
            ];
            $notification_2 = [
                'type_id' => 154919,
                'type' => 'Job',
                'text' => 'Pure Milk',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1549878615_dairy_milk_and_ghee.png',
                'statuses' => [
                    [
                        'text' => 'Your order is in cancelled',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order is in process',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png',
                        'date' => '30 Jul at 12:47 PM',
                    ],
                    [
                        'text' => 'Your order has been accepted',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/accepted.png',
                        'date' => '30 Jul at 12:47 PM',
                    ]
                ]
            ];


            array_push($notifications, $notification_1, $notification_2);
            return api_response($request, null, 200, ['notifications' => $notifications]);
        } catch (\Throwable $e) {
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