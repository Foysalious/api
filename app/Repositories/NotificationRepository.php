<?php

namespace App\Repositories;

use App\Models\Job;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;

class NotificationRepository
{
    private $order;
    private $sender_id;
    private $sender_type;

//    public function __construct($order)
//    {
//        $this->order = $order;
//        $this->send();
//    }

    public function send($order)
    {
        $this->order = $order;
        if ($this->order->sales_channel == 'Web') {
            $this->sender_id = $this->order->customer_id;
            $this->sender_type = 'customer';

            $this->sendNotificationToBackEnd();
        } else {
            $this->sender_id = $this->order->created_by;
            $this->sender_type = 'user';

            $this->sendNotificationToCRM(); //REMOVE
        }
        $this->sendNotificationToPartner($this->order->partner_orders);
    }

    private function sendNotificationToCRM()
    {
        notify()->user($this->order->jobs->first()->crm_id)->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'You have been assigned to a new order - ' . $this->order->code(),
            'link' => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type' => notificationType('Info')
        ]);
    }

    private function sendNotificationToBackEnd()
    {
        notify()->departments([5, 7])->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'New Order Placed From Front End - ' . $this->order->code(),
            'link' => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type' => notificationType('Info')
        ]);
    }

    private function sendNotificationToPartner($partner_orders)
    {
        foreach ($partner_orders as $partner_order) {
            $partner = Partner::find($partner_order->partner_id);
            notify()->partner($partner->id)->sender($this->sender_id, $this->sender_type)->send([
                'title' => 'New Order Placed ID ' . $partner_order->code(),
                'link' => env('SHEBA_PARTNER_END_URL') . '/' . $partner->sub_domain . '/order/' . $partner_order->id,
                'type' => notificationType('Info'),
                'event_type' => "App\Models\PartnerOrder",
                'event_id' => $partner_order->id,
                'version' => $partner_order->getVersion()
            ]);
            (new PushNotificationRepository())->send([
                "title" => 'New Order',
                "message" => 'New Order Placed ID ' . $partner_order->code(),
                "event_type" => 'PartnerOrder',
                "event_id" => $partner_order->id,
                "link" => "new_order"
            ], constants('MANAGER_TOPIC_NAME') . $partner_order->partner_id);
        }
    }

    public function forOnlinePayment($partner_order, $amount)
    {
        try {
            $partner_order = ($partner_order instanceof PartnerOrder) ? $partner_order : PartnerOrder::find($partner_order);
            $this->order = $partner_order->order;
            $this->sender_id = $this->order->customer_id;
            $this->sender_type = 'customer';
            $this->_sendPaymentNotificationToCM($partner_order, $amount);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function _sendPaymentNotificationToCM($partner_order, $amount)
    {
        notify()->user($partner_order->jobs[0]->crm_id)->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'An online payment of ' . $amount . ' has been completed to this order ' . $this->order->code(),
            'link' => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type' => notificationType('Info')
        ]);
    }

    public function forAffiliateRegistration($affiliate)
    {
        notify()->departments([3, 8])->send([
            'title' => 'New Affiliate Registration from ' . $affiliate->profile->mobile,
            'link' => env('SHEBA_BACKEND_URL') . '/affiliate/' . $affiliate->id,
            'type' => notificationType('Info')
        ]);
    }

    public function forAffiliation($affiliate, $affiliation)
    {
        notify()->department(7)->send([
            'title' => 'New Affiliation Arrived from ' . $affiliate->profile->mobile,
            'link' => env('SHEBA_BACKEND_URL') . '/affiliation/' . $affiliation->id,
            'type' => notificationType('Info')
        ]);
    }

    public function getManagerNotifications($model, $offset, $limit)
    {
        $notifications = $model->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
        if (count($notifications) > 0) {
            $notifications = $notifications->map(function ($notification) {
                $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
                array_add($notification, 'time', $notification->created_at->format('j M \\a\\t h:i A'));
                if ($notification->event_type == 'Job') {
                    if (!stristr($notification->title, 'cancel')) {
                        $job = Job::find($notification->event_id);
                        $notification->event_type = 'PartnerOrder';
                        $notification->event_id = $job->partner_order->id;
                        $notification->event_code = $job->partner_order->code();
                        $notification->status = (($job->partner_order)->calculate(true))->status;
                        array_add($notification, 'version', $job->partner_order->getVersion());
                    } else {
                        $notification->event_type = null;
                        $notification->event_id = null;
                    }
                } elseif ($notification->event_type == 'Order') {
                    array_add($notification, 'event_code', (Order::find($notification->event_id))->code());
                } elseif ($notification->event_type == 'PartnerOrder') {
                    $partner_order = PartnerOrder::find($notification->event_id);
                    array_add($notification, 'event_code', $partner_order->code());
                    array_add($notification, 'version', $partner_order->getVersion());
                    $notification->status = ((PartnerOrder::find($notification->event_id))->calculate(true))->status;
                }
                return $notification;
            });
        }
        return $notifications;
    }

    public function sendToCRM($cm_id, $title, $model)
    {
        notify()->user($cm_id)->send([
            'title' => $title,
            'link' => env('SHEBA_BACKEND_URL') . '/' . strtolower(class_basename($model)) . '/' . $model->id,
            'type' => notificationType('Info')
        ]);
    }

}