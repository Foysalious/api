<?php namespace App\Repositories;

use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Sheba\Affiliate\PushNotification\MovieTicketPurchaseFailed;
use App\Sheba\Affiliate\PushNotification\TopUpFailed;
use App\Sheba\Affiliate\PushNotification\TransportTicketPurchaseFailed;
use App\Sheba\Subscription\Partner\PartnerSubscriptionChange;
use Sheba\PushNotificationHandler;
use Sheba\Subscription\Partner\BillingType;

class NotificationRepository
{
    private $order;
    private $sender_id;
    private $sender_type;

    /*public function __construct($order)
    {
        $this->order = $order;
        $this->send();
    }*/
    public function send($order)
    {
        $this->order = $order;
        if (in_array($this->order->sales_channel, [
            'Web',
            'App',
            'App-iOS',
            'E-Shop'
        ])) {
            $this->sender_id   = $this->order->customer_id;
            $this->sender_type = 'customer';

            //$this->sendNotificationToBackEnd();
        } else {
            $this->sender_id   = $this->order->created_by;
            $this->sender_type = 'user';

            //$this->sendNotificationToCRM(); //REMOVE
        }
        if (!$this->order->jobs->first()->resource_id)
            $this->sendNotificationToPartner($this->order->partner_orders);
    }

    private function sendNotificationToPartner($partner_orders)
    {
        foreach ($partner_orders as $partner_order) {
            $partner = Partner::find($partner_order->partner_id);
            notify()->partner($partner->id)->sender($this->sender_id, $this->sender_type)->send([
                'title'      => 'New Order Placed ID ' . $partner_order->code(),
                'link'       => env('SHEBA_PARTNER_END_URL') . '/' . $partner->sub_domain . '/order/' . $partner_order->id,
                'type'       => notificationType('Info'),
                'event_type' => "App\Models\PartnerOrder",
                'event_id'   => $partner_order->id,
                //'version' => $partner_order->getVersion()
            ]);
            $topic   = config('sheba.push_notification_topic_name.manager') . $partner_order->partner_id;
            $channel = config('sheba.push_notification_channel_name.manager');
            $sound   = config('sheba.push_notification_sound.manager');
            (new PushNotificationHandler())->send([
                "title"      => 'New Order',
                "message"    => "প্রিয় $partner->name আপনার একটি নতুন অর্ডার রয়েছে " . $partner_order->code() . ", অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
                "event_type" => 'PartnerOrder',
                "event_id"   => $partner_order->id,
                "link"       => "new_order",
                "sound"      => "notification_sound",
                "channel_id" => $channel
            ], $topic, $channel, $sound);
        }
    }

    public function updateSeenBy($by, $notifications)
    {
        foreach ($notifications as $notification) {
            $notification->timestamps = false;
            $notification->is_seen    = 1;
            $notification->update();
        }
    }

    public function forOnlinePayment($partner_order, $amount)
    {
        try {
            $partner_order     = ($partner_order instanceof PartnerOrder) ? $partner_order : PartnerOrder::find($partner_order);
            $this->order       = $partner_order->order;
            $this->sender_id   = $this->order->customer_id;
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
            'link'  => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type'  => notificationType('Info')
        ]);
    }

    public function forAffiliateRegistration($affiliate)
    {
        try {
            notify()->departments([
                3,
                8
            ])->send([
                'title' => 'New Affiliate Registration from ' . $affiliate->profile->mobile,
                'link'  => env('SHEBA_BACKEND_URL') . '/affiliate/' . $affiliate->id,
                'type'  => notificationType('Info')
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function forAffiliation($affiliate, $affiliation)
    {
        try {
            notify()->department(7)->send([
                'title' => 'New Affiliation Arrived from ' . $affiliate->profile->mobile,
                'link'  => env('SHEBA_BACKEND_URL') . '/affiliation/' . $affiliation->id,
                'type'  => notificationType('Info')
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function forPartnerAffiliation($affiliate, $partner_affiliation)
    {
        notify()->department(7)->send([
            'title'      => 'New SP Referral Arrived from ' . $affiliate->profile->mobile,
            'link'       => env('SHEBA_BACKEND_URL') . '/partner-affiliation/' . $partner_affiliation->id,
            'type'       => notificationType('Info'),
            'event_type' => "App\\Models\\" . class_basename($affiliate),
            'event_id'   => $partner_affiliation->id
        ]);
    }

    /**
     * @param $model
     * @param $notification_id
     * @return array
     */
    public function getUnseenNotifications($model, $notification_id)
    {
        $unseen_notifications = $model->notifications()->where('is_seen', '0')->select('id')->orderBy('id', 'desc')->get();
        $index                = 0;
        if (!$unseen_notifications->isEmpty() && $unseen_notifications[0]->id == $notification_id) {
            $index = 1;
        }
        return [
            'next_notification' => !$unseen_notifications->isEmpty() ? $unseen_notifications[$index]->id : null,
            'total_unseen'      => count($unseen_notifications),
        ];
    }

    public function sendToCRM($cm_id, $title, $model)
    {
        try {
            notify()->user($cm_id)->send([
                'title' => $title,
                'link'  => env('SHEBA_BACKEND_URL') . '/' . strtolower(class_basename($model)) . '/' . $model->id,
                'type'  => notificationType('Info')
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    public function sendInsufficientNotification(Partner $partner, $package, $package_type, $grade, $withMessage = true)
    {
        $title     = ' অপর্যাপ্ত  ব্যলেন্স';
        $type      = BillingType::BN()[$package_type];
        $gradeType = $grade == PartnerSubscriptionChange::UPGRADE ? " এর" : $grade == PartnerSubscriptionChange::RENEWED ? " নাবায়ন এর" : " এর";
        $message   = "এসম্যানেজার এর $type $package->show_name_bn প্যকেজ এ সাবস্ক্রিপশন $gradeType  জন্য আপনার ওয়ালেট এ  পর্যাপ্ত  ব্যলেন্স নেই আনুগ্রহ করে ওয়ালেট রিচার্জ করুন এবং সাবস্ক্রিপশন সক্রিয় করুন।";
        $this->sendSubscriptionNotification($title, $message, $partner);
        if ($withMessage) {
            (new SmsHandler('insufficient-balance-subscription'))->send($partner->getContactNumber(), [
                'package_type_bn' => $type,
                'package_name'    => $package->show_name_bn,
                'grade_text'      => $gradeType
            ]);
        }
    }

    public function sendSubscriptionNotification($title, $message, Partner $partner)
    {
        $topic   = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound   = config('sheba.push_notification_sound.manager');
        (new PushNotificationHandler())->send([
            'title'      => $title,
            'message'    => $message,
            'event_type' => 'Subscription',
            'event_id'   => $partner->id,
            'link'       => $partner->subscription ? $partner->subscription->name : 'LITE',
            "sound"      => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
    }

    public function pushNotificationToAffiliate($type,$agent_id,$mobile)
    {
        switch ($type) {
            case 'topup_failed': return (new TopupFailed($agent_id,$mobile))->send();
            case 'purchase_movie_ticket_failed': return (new MovieTicketPurchaseFailed($agent_id,$mobile))->send();
            case 'purchase_transport_ticket_failed': return (new TransportTicketPurchaseFailed($agent_id,$mobile))->send();
        }
    }

    private function sendNotificationToCRM()
    {
        notify()->user($this->order->jobs->first()->crm_id)->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'You have been assigned to a new order - ' . $this->order->code(),
            'link'  => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type'  => notificationType('Info')
        ]);
    }

    private function sendNotificationToBackEnd()
    {
        notify()->departments([
            5,
            7
        ])->sender($this->sender_id, $this->sender_type)->send([
            'title' => 'New Order Placed From Front End - ' . $this->order->code(),
            'link'  => env('SHEBA_BACKEND_URL') . '/order/' . $this->order->id,
            'type'  => notificationType('Info')
        ]);
    }
}
