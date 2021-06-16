<?php namespace App\Repositories;


use App\Models\Affiliate;
use App\Models\Job;
use App\Models\Notification;
use App\Models\OfferShowcase;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Sheba\Affiliate\PushNotification\MovieTicketPurchaseFailed;
use App\Sheba\Affiliate\PushNotification\TopUpFailed;
use App\Sheba\Affiliate\PushNotification\TransportTicketPurchaseFailed;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use App\Sheba\Subscription\Partner\PartnerSubscriptionChange;
use Carbon\Carbon;
use LaravelFCM\Message\Exceptions\InvalidOptionsException;
use Sheba\Dal\PushNotificationMonitoring\PushNotificationMonitoringItem;
use Sheba\PartnerOrderRequest\Events\OrderRequestEvent;
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
    /**
     * @param $order
     * @throws InvalidOptionsException
     */
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

    /**
     * @param $partner_orders
     * @throws InvalidOptionsException
     */
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
            $payload=[
                "title"      => 'New Order',
                "message"    => "প্রিয় $partner->name আপনার একটি নতুন অর্ডার রয়েছে " . $partner_order->code() . ", অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
                "event_type" => 'PartnerOrder',
                "event_id"   => $partner_order->id,
                "link"       => "new_order",
                "sound"      => "notification_sound",
                "channel_id" => $channel,
                'create_time'=>Carbon::now()->format('Y-m-d H:i:s')
            ];
            $notification                          = (new PushNotificationMonitoringItem())->create(['partner_id' => $partner->id, 'sent_payload' => json_encode($payload)]);
            $payload['notification_monitoring_id'] = $notification ? $notification->id : null;
            $topic_response=(new PushNotificationHandler())->setPriority(true)->send($payload, $topic, $channel, $sound);
            if ($topic_response) {
                $notification->update(['sent_payload' => json_encode($payload), 'topic_message_id' => $topic_response->isSuccess()]);
            }

            event(new OrderRequestEvent(['user_type' => 'partner', 'user_id' => $partner->id, 'payload' => $payload]));
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
            'link'  => config('sheba.admin_url') . '/order/' . $this->order->id,
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
                'link'  => config('sheba.admin_url') . '/affiliate/' . $affiliate->id,
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
                'link'  => config('sheba.admin_url') . '/affiliation/' . $affiliation->id,
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
            'link'       => config('sheba.admin_url') . '/partner-affiliation/' . $partner_affiliation->id,
            'type'       => notificationType('Info'),
            'event_type' => "App\\Models\\" . class_basename($affiliate),
            'event_id'   => $partner_affiliation->id
        ]);
    }

    /**
     * @param $model
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function getManagerNotifications($model, $offset, $limit)
    {
        $notifications = $model->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->orderBy('id', 'desc')->skip($offset)->limit($limit);
        if (request()->header('portal-name') == 'manager-app')
            $notifications = $notifications->where('event_type', 'not like', '%procurement%');
        $notifications = $notifications->get();
        if (count($notifications) > 0) {
            $notifications = $notifications->map(function ($notification) {
                $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
                array_add($notification, 'time', $notification->created_at->format('j M \\a\\t h:i A'));
                $icon = $this->getNotificationIcon($notification->event_id, $notification->type);
                array_add($notification, 'icon', $icon);
                if ($notification->event_type == 'Job') {
                    if (!stristr($notification->title, 'cancel')) {
                        $job                      = Job::find($notification->event_id);
                        $notification->event_type = 'PartnerOrder';
                        $notification->event_id   = $job->partner_order->id;
                        $notification->event_code = $job->partner_order->code();
                        $notification->status     = (($job->partner_order)->calculate(true))->status;
                        array_add($notification, 'version', $job->partner_order->getVersion());
                    } else {
                        $notification->event_type = null;
                        $notification->event_id   = null;
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

    /**
     * @param $event_id
     * @param $type
     * @return mixed|string
     */
    protected function getNotificationIcon($event_id, $type)
    {
        $offer = OfferShowcase::query()->where('id', $event_id)->first();
        if ($offer && $offer->thumb != '')
            return $offer->thumb;
        if (in_array(config('constants.NOTIFICATION_ICONS.' . $type), config('constants.NOTIFICATION_ICONS')))
            return getCDNAssetsFolder() . config('constants.NOTIFICATION_ICONS.' . $type);
        return getCDNAssetsFolder() . config('constants.NOTIFICATION_ICONS.Default');
    }

    /**
     * @param $notification_id
     * @return array
     */
    public function getManagerNotification($notification_id)
    {
        try {
            $notification             = Notification::find($notification_id);
            $notification->timestamps = false;
            $notification->is_seen    = 1;
            $notification->save();
            $event = app($notification->event_type);
            if ($event) {
                $offer = $notification->event_type && $notification->event_id ? $event::find($notification->event_id) : new OfferShowcase();
                return [
                    'banner'      => $offer->app_banner ? $offer->app_banner : null,
                    'title'       => $offer->title ? $offer->title : $notification->title ?: config('constants.NOTIFICATION_DEFAULTS.title'),
                    'type'        => $notification->type ? $notification->type : config('constants.NOTIFICATION_DEFAULTS.type'),
                    'description' => $offer->detail_description ? strip_tags($offer->detail_description) : config('constants.NOTIFICATION_DEFAULTS.short_description'),
                    'button_text' => $offer->button_text ? $offer->button_text : config('constants.NOTIFICATION_DEFAULTS.button_text'),
                    "target_link" => $offer->target_link ? $offer->target_link : config('constants.NOTIFICATION_DEFAULTS.target_link'),
                    "target_type" => $offer->target_type ? str_replace('App\Models\\', "", $offer->target_type) : 'dummy target type',
                    "target_id"   => $offer->target_id ? $offer->target_id : 'dummy target id',
                ];
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
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
                'link'  => config('sheba.admin_url') . '/' . strtolower(class_basename($model)) . '/' . $model->id,
                'type'  => notificationType('Info')
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    public function sendInsufficientNotification(Partner $partner, $package, $package_type, $grade, $withMessage = true)
    {
        $title     = ' অপর্যাপ্ত  ব্যলেন্স';
        $type      = $package->titleTypeBn($package_type);
        $gradeType = $grade == PartnerSubscriptionChange::UPGRADE ? " এর" : $grade == PartnerSubscriptionChange::RENEWED ? " নাবায়ন এর" : " এর";
        $message   = "এসম্যানেজার এর $type $package->show_name_bn প্যকেজ এ সাবস্ক্রিপশন $gradeType  জন্য আপনার ওয়ালেট এ  পর্যাপ্ত  ব্যলেন্স নেই আনুগ্রহ করে ওয়ালেট রিচার্জ করুন এবং সাবস্ক্রিপশন সক্রিয় করুন।";
        $this->sendSubscriptionNotification($title, $message, $partner);
        if ($withMessage) {
            (new SmsHandler('insufficient-balance-subscription'))
                ->setBusinessType(BusinessType::SMANAGER)
                ->setFeatureType(FeatureType::PARTNER_SUBSCRIPTION)
                ->send($partner->getContactNumber(), [
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
            'link'  => config('sheba.admin_url') . '/order/' . $this->order->id,
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
            'link'  => config('sheba.admin_url') . '/order/' . $this->order->id,
            'type'  => notificationType('Info')
        ]);
    }
}
