<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Description;
use Sheba\Logs\Customer\JobLogs;

class CustomerNotificationController extends Controller
{

    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer;

            /*$orders = $customer->orders()->with(['partnerOrders' => function ($q) {
                $q->with(['jobs' => function ($q) {
                    $q->with(['statusChangeLogs' => function ($q) {
                        $q->select('id', 'job_id', 'to_status', 'created_at');
                    }])->select('id', 'partner_order_id', 'category_id', 'status', 'service_id', 'created_at');
                }])->select('id', 'order_id', 'cancelled_at');
            }])->whereHas('partnerOrders', function ($q) {
                $q->whereNull('cancelled_at');
            })->get();

            $notifications = [];
            foreach ($orders as $order) {
                foreach ($order->partnerOrders as $partnerOrder) {
                    foreach ($partnerOrder->jobs as $job) {
                        $category = $job->category == null ? $job->service->category : $job->category;
                        $status_change_logs = $job->statusChangeLogs()
                            ->select('id', 'job_id', 'to_status', 'created_at')
                            ->whereIn('to_status', ['Served', 'Process', 'Accepted', 'Cancelled'])
                            ->orderBy('created_at', 'desc')
                            ->get();
                        $status_logs = [];
                        foreach ($status_change_logs as $log) {
                            $icon = null;
                            if ($log->to_status == 'Served') {
                                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png';
                            } elseif ($log->to_status == 'Process') {
                                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png';
                            } elseif ($log->to_status == 'Accepted') {
                                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/accepted.png';
                            } else {
                                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png';
                            }
                            $status_log = [
                                'text' => "Order $log->to_status successfully",
                                'icon' => $icon,
                                'date' => $log->created_at->format("d M") . ' at ' . $log->created_at->format("h:i A"),
                            ];
                            array_push($status_logs, $status_log);
                        }
                        $notification = [
                            'type' => 'job',
                            'type_id' => $job->id,
                            'text' => $category->name,
                            'image' => $category->thumb,
                            'statuses' => $status_logs
                        ];
                        array_push($notifications, $notification);
                    }
                }
            }*/

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
                    ]
                ]
            ];
            $notification_3 = [
                'type_id' => 154919,
                'type' => 'Job',
                'text' => 'Pure Milk',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1549878615_dairy_milk_and_ghee.png',
                'statuses' => [
                    [
                        'text' => 'Your order is in cancelled',
                        'icon' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png',
                        'date' => '30 Jul at 12:47 PM',
                    ]
                ]
            ];
            $notification_4 = [
                'type_id' => 154919,
                'type' => 'Job',
                'text' => 'Pure Milk',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/1549878615_dairy_milk_and_ghee.png',
                'statuses' => []
            ];

            array_push($notifications, $notification_1, $notification_2,$notification_3,$notification_4);
            return api_response($request, null, 200, ['notifications' => $notifications]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getJobOfOrders($orders)
    {
        $all_jobs = collect();
        foreach ($orders as $order) {
            foreach ($order->partnerOrders as $partnerOrder) {
                foreach ($partnerOrder->jobs as $job) {
                    $category = $job->category == null ? $job->service->category : $job->category;
                    $all_jobs->push(collect(array(
                        'job_id' => $job->id,
                        'category_name' => $category->name,
                        'category_thumb' => $category->thumb,
                        'status' => $job->status,
                        'created_at' => $job->created_at->format('Y-m-d'),
                        'status_change_logs' => $job->statusChangeLogs
                    )));
                }
            }
        }
        return $all_jobs;
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