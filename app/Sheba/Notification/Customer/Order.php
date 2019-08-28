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
                        }])->select('id', 'partner_order_id', 'category_id', 'status', 'service_id', 'created_at');
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
                    if (count($status_logs) > 0) {
                        $notification = [
                            'type' => 'job',
                            'type_id' => $job->id,
                            'text' => $category->name,
                            'image' => $category->thumb,
                            'created_at' => $job->created_at->toDateTimeString(),
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