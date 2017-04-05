<?php

namespace App\Repositories;

use App\Models\Partner;

class NotificationRepository
{
    private $order;
    private $sender_id;
    private $sender_type;

    public function __construct($order)
    {
        $this->order = $order;
        $this->send();
    }

    private function send()
    {
        if ($this->order->sales_channel == 'Web') {
            $this->sender_id = $this->order->customer_id;
            $this->sender_type = 'customer';

            $this->sendNotificationToBackEnd();
        } else {
            $this->sender_id = $this->order->created_by;
            $this->sender_type = 'user';

            $this->sendNotificationToCRM($this->order->jobs);
        }
        $this->sendNotificationToPartner($this->order->partner_orders);
    }

    private function sendNotificationToCRM($jobs)
    {
        foreach ($jobs as $job) {
            notify()->user($job->crm_id)->sender($this->sender_id, $this->sender_type)->send([
                'title' => 'You have been assigned to a new job',
                'link' => env('SHEBA_BACKEND_URL') . '/job/' . $job->id,
                'type' => notificationType('Info')
            ]);
        }
    }

    private function sendNotificationToBackEnd()
    {
        notify()->departments([5, 7])->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'New Order Placed From Front End',
            'link' => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type' => notificationType('Info')
        ]);
    }

    private function sendNotificationToPartner($partner_orders)
    {
        foreach ($partner_orders as $partner_order) {
            $partner = Partner::find($partner_order->partner_id);
            notify()->partner($partner->id)->sender($this->sender_id, $this->sender_type)->send([
                'title' => 'New Order Placed',
                'link' => env('SHEBA_PARTNER_END_URL') . '/' . $partner->sub_domain . '/order/' . $this->order->id,
                'type' => notificationType('Info')
            ]);
        }
    }

}