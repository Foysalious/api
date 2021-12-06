<?php namespace Sheba\Pos\Notifier;


use App\Models\PosOrder;
use Sheba\PushNotificationHandler;
use Sheba\PushNotification\PushNotificationHandler as PusNotificationService;

class WebstorePushNotificationHandler
{
    /**
     * @var PosOrder
     */
    protected $order;

    public function setOrder(PosOrder $order)
    {
        $this->order = $order->calculate();
        return $this;
    }

    public function handle()
    {
        $topic = config('sheba.push_notification_topic_name.manager_new') . $this->order->partner_id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');
        $this->order->calculate();
        $net_bill = $this->order->getNetBill();
        $payment_status = $this->order->getPaid() ? 'প্রদত্ত' : 'বকেয়া';
        $order_id = $this->order->id;
        $partner_wise_order_id = $this->order->partner_wise_order_id;
        $sales_channel = 'অনলাইন স্টোর';
        $data = [
            "title" => 'New Online Store Order',
            "message" => "অর্ডার # $partner_wise_order_id: নতুন অর্ডার দেওয়া হয়েছে। মোট টাকার পরিমাণ: $net_bill ($payment_status)\r\n চ্যানেল: $sales_channel",
            "sound" => $sound,
            "event_type" => 'WebstoreOrder',
            "event_id" => (string)$order_id
        ];
//        (new PushNotificationHandler())->send($data, $topic, $channel, $sound);
        (new PusNotificationService())->send($topic, null, $data);
    }

}