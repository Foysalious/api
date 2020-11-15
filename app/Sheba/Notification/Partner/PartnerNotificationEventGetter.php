<?php namespace Sheba\Notification\Partner;

use App\Models\Job;
use App\Models\Notification;
use App\Models\OfferShowcase;
use App\Models\Order;
use App\Models\PartnerOrder;
use Throwable;

class PartnerNotificationEventGetter
{
    private $notification;
    private $eventType;
    const EXTERNAL_BUTTON_TEXT = 'View details';
    const EXTERNAL_TYPE   = 'ExternalProject';
    const NEW_PROCUREMENT = 'NEW_PROCUREMENT';

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        $this->eventType    = $notification->event_type;
    }

    public function setEventData()
    {
        $this->notification->event_type = str_replace('App\Models\\', "", $this->eventType);
        $eventDataFunction = "set{$this->notification->event_type}Data";
        if (isset($this->$eventDataFunction) && is_callable($this->$eventDataFunction)) {
            $this->$eventDataFunction();
        }
        return $this;
    }

    private function setJobData()
    {
        if (!stristr($this->notification->title, 'cancel')) {
            $job                            = Job::find($this->notification->event_id);
            $this->notification->event_type = 'PartnerOrder';
            $this->notification->event_id   = $job->partner_order->id;
            $this->notification->event_code = $job->partner_order->code();
            $this->notification->status     = (($job->partner_order)->calculate(true))->status;
            $this->notification->version    = $job->partner_order->getVersion();
            return;
        }

        $this->notification->event_type = null;
        $this->notification->event_id   = null;
    }

    private function setOrderData()
    {
        $this->notification->event_code = (Order::find($this->notification->event_id))->code();
    }

    private function setPartnerOrderData()
    {
        $partner_order = PartnerOrder::find($this->notification->event_id);
        $this->notification->event_code = $partner_order->code();
        $this->notification->version    = $partner_order->getVersion();
        $this->notification->status     = ((PartnerOrder::find($this->notification->event_id))->calculate(true))->status;
    }

    public function get()
    {
        return $this->notification;
    }

    public function getDetails()
    {
        try {
            $eventType = app($this->eventType);
            if ($eventType) {
                $event = $eventType::find($this->notification->event_id);

                if ($event instanceof OfferShowcase) return $this->getDetailsFromOffer($event);
                if ($event instanceof PartnerOrder) return $this->getDetailsPartnerOrder($event);
                return $this->getDetailsFromNotification();
            }
            return $this->getDetailsFromNotification();
        } catch (Throwable $e) {
            return $this->getDetailsFromNotification();
        }
    }

    private function getDetailsFromNotification()
    {
        $notification = constants('NOTIFICATION_DEFAULTS');
        $defaultFromNotification = ['title' => $this->notification->title, 'description' => $this->notification->description ?: $notification['description'], 'target_link' => $this->notification->link, 'target_type' => str_replace('App\Models\\', "", $this->eventType), 'target_id' => $this->notification->event_id];
        if ($this->eventType == self::NEW_PROCUREMENT) {
            $defaultFromNotification['target_type'] = self::EXTERNAL_TYPE;
            $defaultFromNotification['target_id']   = self::EXTERNAL_TYPE;
            $defaultFromNotification['button_text'] = self::EXTERNAL_BUTTON_TEXT;
            $defaultFromNotification['banner']      = config('partner.procurement_banner');
        }
        $notification = array_merge($notification, $defaultFromNotification);

        return $notification;
    }

    private function getDetailsFromOffer(OfferShowcase $offer)
    {
        return [
            'banner'      => $offer->app_banner ?: config('constants.NOTIFICATION_DEFAULTS.banner'),
            'title'       => $offer->title ? $offer->title : $this->notification->title ?: config('constants.NOTIFICATION_DEFAULTS.title'),
            'type'        => $this->notification->type ?: config('constants.NOTIFICATION_DEFAULTS.type'),
            'description' => $offer->detail_description ? $offer->detail_description : config('constants.NOTIFICATION_DEFAULTS.short_description'),
            'button_text' => $offer->button_text ? $offer->button_text : config('constants.NOTIFICATION_DEFAULTS.button_text'),
            "target_link" => $offer->target_link ? $offer->target_link : config('constants.NOTIFICATION_DEFAULTS.target_link'),
            "target_type" => $offer->target_type ? str_replace('App\Models\\', "", $offer->target_type) : config('constants.NOTIFICATION_DEFAULTS.target_type'),
            "target_id"   => $offer->target_id ? $offer->target_id : '',
        ];
    }

    private function getDetailsPartnerOrder(PartnerOrder $order)
    {
        $notification = constants('NOTIFICATION_DEFAULTS');
        return [
            'banner'      => $this->notification->app_banner ?: config('constants.NOTIFICATION_DEFAULTS.banner'),
            'title' => $this->notification->title,
            'short_description' => $this->notification->short_description ?: $notification['short_description'],
            'description' => $this->notification->description ?: $notification['description'],
            'button_text'       => 'View Order Details',
            'type'              => $this->notification->type ?: $notification['description'],
            'target_link' => $this->notification->link,
            'target_type' => str_replace('App\Models\\', "", $this->eventType) ?: $notification['target_type'],
            'target_id' => $this->notification->event_id];
    }
}
