<?php namespace Sheba\Notification\Partner;

use App\Models\Job;
use App\Models\Notification;
use App\Models\OfferShowcase;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Repositories\NotificationRepository;
use Exception;
use Sheba\Notification\SeenBy;

class PartnerNotificationHandler extends Handler
{
    private $portal;
    private $modifier;
    private $repo;

    public function __construct(Partner $partner)
    {
        $this->setModel($partner);
        $this->repo = new NotificationRepository();
    }

    /**
     * @param mixed $modifier
     * @return PartnerNotificationHandler
     */
    public function setModifier($modifier)
    {
        $this->modifier = $modifier;
        return $this;
    }

    /**
     * @param mixed $portal
     * @return PartnerNotificationHandler
     */
    public function setPortal($portal)
    {
        $this->portal = $portal;
        return $this;
    }

    /**
     * @param $offset
     * @param $limit
     * @return array
     */
    public function getList($offset, $limit)
    {
        $notifications_base_query = $this->model->notifications();
        if ($this->portal == 'manager-app')
            $notifications_base_query = $notifications_base_query->where("event_type", "<>", "App\Models\Procurement");

        $notifications = $notifications_base_query
            ->select('id', 'feature_name', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')
            ->orderBy('id', 'desc');

        $unseen = $this->model->notifications()->where('is_seen', '0')->count();
        $notifications = $notifications->skip($offset)->limit($limit)->get();
        $notifications = $notifications->map(function ($notification) {
            return self::getListItem($notification);
        });

        return [$notifications, $unseen];
    }

    /**
     * @param Notification $notification
     * @return Notification
     */
    public static function getListItem(Notification $notification)
    {
        $notification->event_type = str_replace('App\Models\\', "", $notification->event_type);
        $notification->time = $notification->created_at->format('j M \\a\\t h:i A');
        $notification->icon = self::getNotificationIcon($notification->feature_name, $notification->type);
        if ($notification->event_type == 'Offershowcase') {
            $offer = OfferShowcase::query()->where('id', $notification->event_id)->first();
            if ($offer && $offer->thumb != '') $notification->icon = $offer->thumb;
        }

        return (new PartnerNotificationEventGetter($notification))->setEventData()->get();
    }

    /**
     * @param $feature_name
     * @param $type
     * @return mixed|string
     */
    public static function getNotificationIcon($feature_name, $type)
    {
        if ($feature_name == 'default') {
            if (in_array(config('constants.NOTIFICATION_ICONS.' . $type), config('constants.NOTIFICATION_ICONS')))
                return getCDNAssetsFolder() . config('constants.NOTIFICATION_ICONS.' . $type);
            return getCDNAssetsFolder() . config('constants.NOTIFICATION_ICONS.Default');
        }

        return getCDNAssetsFolder() . config('constants.NOTIFICATION_ICONS.' . $feature_name);
    }

    /**
     * @param $notification
     * @return array
     */
    public function getDetails($notification)
    {
        $notification = (!$notification instanceof Notification) ? $this->model->notifications()->find($notification) : $notification;
        (new SeenBy())->setSeen($notification);

        return [
            (new PartnerNotificationEventGetter($notification))->getDetails(),
            $this->repo->getUnseenNotifications($this->model, $notification->id)
        ];
    }

    /**
     * @param array $partner_ids
     * @throws Exception
     */
    public function notifyForProcurement(array $partner_ids)
    {
        notify()->partners($partner_ids)->send([
            'event_type' => $this->eventType ?: PartnerNotificationEventGetter::NEW_PROCUREMENT,
            'event_id' => $this->eventId ?: null,
            'title' => $this->title,
            'link' => $this->link,
            'description' => $this->description,
            'type' => notificationType('Info')
        ]);
    }
}
