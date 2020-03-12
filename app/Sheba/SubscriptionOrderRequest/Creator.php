<?php namespace Sheba\SubscriptionOrderRequest;

use App\Models\Partner;
use App\Models\SubscriptionOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
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
     * @param SubscriptionOrderRequestRepositoryInterface $repo
     * @param ImpressionManager $impressionManager
     * @param PushNotificationHandler $push_notification_handler
     */
    public function __construct(SubscriptionOrderRequestRepositoryInterface $repo, ImpressionManager $impressionManager, PushNotificationHandler $push_notification_handler)
    {
        $this->repo = $repo;
        $this->impressionManager = $impressionManager;
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
    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @return void
     */
    public function create()
    {
        $this->subscriptionOrderRequestId = $this->repo->create([
            'subscription_order_id' => $this->subscriptionOrder->id,
            'partner_id' => $this->partner->id
        ]);
        $this->sendOrderRequestSmsToPartner($this->partner);
        $this->sendOrderRequestPushNotificationToPartner($this->partner);
        $this->impressionManager->setLocationId($this->subscriptionOrder->location_id)->setCategoryId($this->subscriptionOrder->category_id)
            ->setCustomerId($this->subscriptionOrder->customer_id)->setPortalName(request()->header('portal-name'))
            ->setServices($this->subscriptionOrder->service_details->toArray())->deduct([$this->partner->id]);
    }

    /**
     * @param $partner
     */
    private function sendOrderRequestPushNotificationToPartner($partner)
    {
        $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');
        $this->pushNotificationHandler->send([
            "title" => 'New Order',
            "message" => "প্রিয় $partner->name আপনার একটি নতুন অর্ডার রয়েছে, অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
            "sound" => "notification_sound",
            "event_type" => 'SubscriptionOrder', //TODO: Need to check if this will serve the purpose or not
            "event_id" => $this->subscriptionOrderRequestId,
            "link" => "new_order"
        ], $topic, $channel, $sound);
    }

    private function sendOrderRequestSmsToPartner($partner)
    {
        /** @var Partner $partner */
        (new SmsHandlerRepo('partner-order-request'))->send($partner->getContactNumber(), [
            'partner_name' => $partner->name
        ]);
    }
}
