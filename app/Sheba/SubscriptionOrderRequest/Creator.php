<?php namespace Sheba\SubscriptionOrderRequest;

use App\Models\Partner;
use App\Models\SubscriptionOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Carbon\Carbon;
use Sheba\Dal\PushNotificationMonitoring\PushNotificationMonitoringItem;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequest;
use Sheba\Dal\SubscriptionOrderRequest\SubscriptionOrderRequestRepositoryInterface;
use Sheba\Partner\ImpressionManager;
use Sheba\PushNotificationHandler;

class Creator
{
    /** @var SubscriptionOrderRequestRepositoryInterface */
    private $repo;
    /** @var SubscriptionOrder */
    private $subscriptionOrder;
    private $partner_id;
    /** @var Partner $partner */
    private $partner;
    /** @var ImpressionManager ImpressionManager */
    private $impressionManager;
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;
    /** @var SubscriptionOrderRequest $subscriptionOrderRequestId */
    private $subscriptionOrderRequestId;

    /**
     * Creator constructor.
     *
     * @param SubscriptionOrderRequestRepositoryInterface $repo
     * @param ImpressionManager                           $impressionManager
     * @param PushNotificationHandler                     $push_notification_handler
     */
    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo, ImpressionManager $impressionManager, PushNotificationHandler $push_notification_handler)
    {
        $this->repo                    = $repo;
        $this->impressionManager       = $impressionManager;
        $this->pushNotificationHandler = $push_notification_handler;
    }

    /**
     * @param SubscriptionOrder $subscription_order
     * @return Creator
     */
    public function setSubscriptionOrder(SubscriptionOrder $subscription_order)
    {
        $this->subscriptionOrder = $subscription_order;
        return $this;
    }

    /**
     * @param Partner $partner
     * @return $this
     */
    public function setPartner($partner_id)
    {
        $this->partner_id = $partner_id;
        $this->partner    = Partner::where('id', $partner_id)->first();
        return $this;
    }

    /**
     * @return void
     */
    public function create()
    {
        $this->subscriptionOrderRequestId = $this->repo->create([
            'subscription_order_id' => $this->subscriptionOrder->id,
            'partner_id'            => $this->partner->id
        ]);
        $this->sendOrderRequestSmsToPartner($this->partner);
        $this->sendOrderRequestPushNotificationToPartner($this->partner);
        $this->impressionManager->setLocationId($this->subscriptionOrder->location_id)->setCategoryId($this->subscriptionOrder->category_id)
                                ->setCustomerId($this->subscriptionOrder->customer_id)->setPortalName(request()->header('portal-name'))
                                ->setServices(array(request()->services))->setImpressionToDeduct(10)->deduct([$this->partner->id]);
    }

    /**
     * @param $partner
     */
    private function sendOrderRequestPushNotificationToPartner($partner)
    {
        $topic         = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel       = config('sheba.push_notification_channel_name.manager');
        $sound         = config('sheba.push_notification_sound.manager');
        $data          = [
            "title"      => 'New Order',
            "message"    => "প্রিয় $partner->name আপনার একটি নতুন অর্ডার রয়েছে, অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
            "sound"      => "notification_sound",
            "event_type" => 'PartnerOrder',
            "event_id"   => $this->subscriptionOrderRequestId,
            "link"       => "new_order",
            'create_time'=>Carbon::now()->format('Y-m-d H:i:s')
        ];
        $notification                          = (new PushNotificationMonitoringItem())->create(['partner_id' => $partner->id, 'sent_payload' => json_encode($data)]);
        $data['notification_monitoring_id'] = $notification ? $notification->id : null;
        $topic_response = $this->pushNotificationHandler->setPriority(1)
                                                       ->send($data, $topic, $channel, $sound);
        if ($topic_response) {
            $notification->update(['sent_payload' => json_encode($data), 'topic_message_id' => $topic_response->isSuccess()]);
        }
    }

    private function sendOrderRequestSmsToPartner($partner)
    {
        /** @var Partner $partner */
        (new SmsHandlerRepo('partner-order-request'))
            ->setBusinessType(BusinessType::SMANAGER)
            ->setFeatureType(FeatureType::PARTNER_SUBSCRIPTION_ORDER_REQUEST)
            ->send($partner->getContactNumber(), [
                'partner_name' => $partner->name
            ]);
    }
}
