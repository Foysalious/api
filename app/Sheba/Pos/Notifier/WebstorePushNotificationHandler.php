<?php namespace Sheba\Pos\Notifier;


use App\Models\PosOrder;
use Sheba\PushNotificationHandler;

class WebstorePushNotificationHandler
{
    /**
     * @var PosOrder
     */
    protected $order;

    public function setOrder(PosOrder $order) {
        $this->order = $order->calculate();
        return $this;
    }

    public function handle()
    {
        $topic   = config('sheba.push_notification_topic_name.manager') . $this->order->partner_id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound   = config('sheba.push_notification_sound.manager');
        $this->order->calculate();
        $net_bill = $this->order->getNetBill();
        $payment_status = $this->order->getPaid() ? 'প্রদত্ত' : 'বকেয়া';
        $order_id = $this->order->id;
        $sales_channel = 'ওয়েবস্টোর';
        $notification_data = [
            "title" => 'New Webstore Order',
            "message" => "অর্ডার # $order_id: নতুন অর্ডার দেওয়া হয়েছে। মোট টাকার পরিমাণ: $net_bill ($payment_status)\r\n চ্যানেল: $sales_channel",
            "sound" => "notification_sound",
            "event_type" => 'WebstoreOrder',
            "event_id" => $order_id
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);
    }

}