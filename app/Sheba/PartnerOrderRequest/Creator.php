<?php namespace Sheba\PartnerOrderRequest;

use App\Models\Partner;
use App\Models\PartnerOrder;
use Sheba\Dal\PartnerOrderRequest\PartnerOrderRequestRepositoryInterface;
use Sheba\PartnerOrderRequest\Validators\CreateValidator;
use Sheba\PushNotificationHandler;

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
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;

    /**
     * Creator constructor.
     * @param PartnerOrderRequestRepositoryInterface $partner_order_request_repo
     * @param CreateValidator $create_validator
     * @param PushNotificationHandler $push_notification_handler
     */
    public function __construct(PartnerOrderRequestRepositoryInterface $partner_order_request_repo,
                                CreateValidator $create_validator,
                                PushNotificationHandler $push_notification_handler)
    {
        $this->partnerOrderRequestRepo = $partner_order_request_repo;
        $this->createValidator = $create_validator;
        $this->pushNotificationHandler = $push_notification_handler;
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
        return $this;
    }

    public function create()
    {
        $data = [];
        foreach ($this->partnersId as $partner_id) {
            $this->sendOrderRequestToPartner($partner_id);
            $data[] = [
                'partner_order_id' => $this->partnerOrder->id,
                'partner_id' => $partner_id
            ];
        }

        $this->partnerOrderRequestRepo->insert($data);
    }

    /**
     * @param $partner_id
     */
    private function sendOrderRequestToPartner($partner_id)
    {
        /** @var Partner $partner */
        $partner = Partner::find($partner_id);
        $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');

        $this->pushNotificationHandler->send([
            "title" => 'New Order',
            "message" => "প্রিয় $partner->name আপনার একটি নতুন অর্ডার রয়েছে, অনুগ্রহ করে ম্যানেজার অ্যাপ থেকে অর্ডারটি একসেপ্ট করুন",
            "sound" => "notification_sound"
        ], $topic, $channel, $sound);
    }
}
