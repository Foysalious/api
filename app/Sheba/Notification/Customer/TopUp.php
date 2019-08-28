<?php namespace App\Sheba\Notification\Customer;

use App\Models\TopUpOrder;

class TopUp extends NotificationHandler
{
    public function getNotification()
    {
        $top_up_orders = TopUpOrder::where([
            ['agent_type', "App\\Models\\Customer"],
            ['agent_id', (int)$this->customer->id],
        ])->select('id', 'agent_type', 'agent_id', 'status', 'created_at', 'updated_at')
            ->orderBy('updated_at', 'DESC')
            ->limit(30)->get();

        foreach ($top_up_orders as $top_up_order) {
            $icon = null;
            if ($top_up_order->status == 'Successful') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png';
            } elseif ($top_up_order->status == 'Initiated') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png';
            } else {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png';
            }

            $notification = [
                'type' => 'top_up',
                'type_id' => $top_up_order->id,
                'text' => 'Top Up',
                'image' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/top_up.png',
                'created_at' => $top_up_order->created_at->toDateTimeString(),
                'statuses' => [
                    [
                        'text' => "Your top up is $top_up_order->status",
                        'icon' => $icon,
                        'date' => $top_up_order->updated_at ? $top_up_order->updated_at->format("d M") . ' at ' . $top_up_order->updated_at->format("h:i A") : $top_up_order->created_at->format("d M") . ' at ' . $top_up_order->created_at->format("h:i A")
                    ]
                ]
            ];
            array_push($this->notifications, $notification);
        }
        return $this->notifications;
    }
}