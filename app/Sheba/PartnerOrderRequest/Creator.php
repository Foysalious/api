<?php namespace Sheba\PartnerOrderRequest;

use App\Models\Job;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Repositories\SmsHandler as SmsHandlerRepo;
use App\Transformers\CustomSerializer;
use App\Transformers\Partner\OrderRequestTransformer;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequest;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\Partner\ImpressionManager;
use Sheba\PartnerOrderRequest\Events\OrderRequestEvent;
use Sheba\PartnerOrderRequest\Validators\CreateValidator;
use Sheba\PushNotificationHandler;
use Throwable;

class Creator
{
    /** @var PartnerOrderRequestRepositoryInterface $partnerOrderRequest */
    private $partnerOrderRequestRepo;
    /** @var CreateValidator $createValidator */
    private $createValidator;
    /** @var PartnerOrder $partnerOrder */
    private $partnerOrder;
    /** @var array $partnersId */
    private $partnersId;
    /** @var Collection $partners */
    private $partners;
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;
    /** @var PartnerOrderRequest $partnerOrderRequestId */
    private $partnerOrderRequestId;
    /** @var ImpressionManager ImpressionManager */
    private $impressionManager;

    public function __construct(PartnerOrderRequestRepositoryInterface $partner_order_request_repo,
                                CreateValidator $create_validator,
                                PushNotificationHandler $push_notification_handler, ImpressionManager $impressionManager)
    {
        $this->partnerOrderRequestRepo = $partner_order_request_repo;
        $this->createValidator         = $create_validator;
        $this->pushNotificationHandler = $push_notification_handler;
        $this->impressionManager       = $impressionManager;
    }

    /**
     * @return array
     */
    public function hasError()
    {
        return $this->createValidator->hasError();
    }

    /**
     * @param PartnerOrder $partner_order
     * @return Creator
     */
    public function setPartnerOrder(PartnerOrder $partner_order)
    {
        $this->partnerOrder = $partner_order;
        return $this;
    }

    /**
     * @param array $partners_id
     * @return $this
     */
    public function setPartners(array $partners_id)
    {
        $this->partnersId = $partners_id;
        $this->partners   = Partner::whereIn('id', $partners_id)->get();
        return $this;
    }

    public function create()
    {
        $data = [];
        foreach ($this->partnersId as $partner_id) {
            $data['partner_order_id']    = $this->partnerOrder->id;
            $data['partner_id']          = $partner_id;
            $this->partnerOrderRequestId = $this->partnerOrderRequestRepo->create($data);
            $this->sendOrderRequestSmsToPartner($partner_id);
            $this->sendOrderRequestPushNotificationToPartner($partner_id);
            $job = $this->partnerOrder->jobs->first();
            $this->impressionManager->setLocationId($this->partnerOrder->order->location_id)->setCategoryId($job->category_id)
                ->setCustomerId($this->partnerOrder->order->customer_id)->setPortalName(request()->header('portal-name'))
                ->setServices($this->getServices($job))->setImpressionToDeduct(10)->deduct([$partner_id]);
        }
    }

    /**
     * @param $partner_id
     */
    private function sendOrderRequestPushNotificationToPartner($partner_id)
    {
        try {
            /** @var Partner $partner */
            $partner = $this->partners->keyBy('id')->get($partner_id);
            $topic   = config('sheba.push_notification_topic_name.manager') . $partner->id;
            $channel = config('sheba.push_notification_channel_name.manager');
            $sound   = config('sheba.push_notification_sound.manager');

            $payload = [
                "title"      => 'New Order',
                "message"    => "প্রিয় $partner->name আপনার একটি নতুন অর্ডার রয়েছে, অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
                "sound"      => "notification_sound",
                "event_type" => 'PartnerOrder',
                "event_id"   => $this->partnerOrderRequestId->id,
                "link"       => "new_order",
                "order"      => $this->getOrderRequestData()
            ];
            $this->pushNotificationHandler->send($payload, $topic, $channel, $sound);
            event(new OrderRequestEvent(['user_type' => 'partner', 'user_id' => $partner->id, 'payload' => $payload]));
        } catch (Throwable $e) {
            logError($e);
        }
    }

    private function getOrderRequestData()
    {
        try {
            $order_request = $this->partnerOrderRequestId;
            $manager       = new Manager();
            $manager->setSerializer(new CustomSerializer());
            $resource = new Item($order_request, new OrderRequestTransformer());
            $data= $manager->createData($resource)->toArray()['data'];
            $data['time_left_to_accept_in_seconds']=config('partner.order.request_accept_time_limit_in_seconds');
            return $data;
        }catch (Throwable $e){
            return [$this->partnerOrderRequestId,$e->getMessage(),$e->getLine()];
        }
    }

    private function sendOrderRequestSmsToPartner($partner_id)
    {
        try {
            /** @var Partner $partner */
            $partner = $this->partners->keyBy('id')->get($partner_id);
            (new SmsHandlerRepo('partner-order-request'))->setVendor('sslwireless')->send($partner->getContactNumber(), [
                'partner_name' => $partner->name
            ]);
        } catch (Throwable $e) {
            logError($e);
        }
    }

    private function getServices(Job $job)
    {
        $serviceArray = [];
        foreach ($job->jobServices as $jobService) {
            array_push($serviceArray, [
                'id'       => $jobService->service_id,
                'quantity' => $jobService->quantity,
                'option'   => $jobService->option
            ]);
        }
        return $serviceArray;
    }
}
