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
            $text = null;
            if ($top_up_order->status == 'Successful') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/served.png';
                $text = 'Your top up is successful';
            } elseif ($top_up_order->status == 'Initiated') {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/Inprocess.png';
                $text = 'Your top up has been Initiated';
            } else {
                $icon = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/cancelled.png';
                $text = 'Your top up is failed';
            }

            $notification = [
                'type' => 'top_up',
                'type_id' => $top_up_order->id,
                'text' => 'Top Up',
                'image' => 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/sheba_xyz/png/notification/top_up.png',
                'updated_at' => $top_up_order->updated_at->toDateTimeString(),
                'statuses' => [
                    [
                        'text' => $text,
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