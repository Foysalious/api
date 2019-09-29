<?php namespace App\Sheba\Notification\Customer;

class Order extends NotificationHandler
{
    public function getNotification()
    {
        $orders = $this->customer->orders()
            ->with(['partnerOrders' => function ($q) {
                $q->with(['jobs' => function ($q) {
                    $q->with([
                        'statusChangeLogs' => function ($q) {
                            $q->select('id', 'job_id', 'to_status', 'created_at')
                                ->whereIn('to_status', ['Served', 'Process', 'Accepted', 'Cancelled'])
                                ->orderBy('created_at', 'desc');
                        },
                        'category' => function ($q) {
                            $q->select('id', 'name', 'thumb');
                        }])->select('id', 'partner_order_id', 'category_id', 'status', 'service_id', 'created_at', 'updated_at');
                }])->select('id', 'order_id', 'cancelled_at');
            }])->select('id', 'customer_id')
            ->whereHas('partnerOrders', function ($q) {
                $q->whereNull('cancelled_at');
            })->orderBy('created_at', 'DESC')
            ->limit(30)
            ->get();

        foreach ($orders as $order) {
            foreach ($order->partnerOrders as $partnerOrder) {
                foreach ($partnerOrder->jobs as $job) {
                    $category = $job->category;
                    $status_change_logs = $job->statusChangeLogs;
                    $status_logs = [];
                    foreach ($status_change_logs as $log) {
                        $icon = null;
                        $text = null;
                        if ($log->to_status == 'Served') {
                            $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png';
                            $text = 'Order served successfully';
                        } elseif ($log->to_status == 'Process') {
                            $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png';
                            $text = 'Your order is in process';
                        } elseif ($log->to_status == 'Accepted') {
                            $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/accepted.png';
                            $text = 'Your order has been accepted';
                        } else {
                            $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png';
                            $text = 'Order has been cancelled';
                        }
                        $status_log = [
                            'text' => $text,
                            'icon' => $icon,
                            'date' => $log->created_at->format("d M") . ' at ' . $log->created_at->format("h:i A"),
                        ];
                        array_push($status_logs, $status_log);
                    }
                    if (count($status_logs) > 0) {
                        $notification = [
                            'type' => 'job',
                            'type_id' => $job->id,
                            'text' => $category->name,
                            'image' => $category->thumb,
                            'updated_at' => $job->updated_at->toDateTimeString(),
                            'statuses' => $status_logs
                        ];
                        array_push($this->notifications, $notification);
                    }
                }
            }
        }
        return $this->notifications;
    }
}