<?php


namespace Sheba\Events;


use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Event;
use Carbon\Carbon;
use Sheba\Location\Geo;
use Sheba\RequestIdentification;

class OutOfZoneEvent extends ShebaEvent
{
    /** @var Geo $geo */
    private $geo;

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function save()
    {
        $event = new Event();
        $event->tag = 'out_of_zone';
        $event->value = json_encode(['lat' => $this->geo->getLat(), 'lng' => $this->geo->getLng()]);
        $event->fill((new RequestIdentification)->get());
        $user_id = $this->getUserId();
        if ($event->portal_name == 'bondhu-app') {
            $event->created_by_type = "App\\Models\\Affiliate";
            if ($user_id) {
                $event->created_by = $user_id;
                $event->created_by_name = "Affiliate - " . (Affiliate::find($user_id))->profile->name;
            }
        } elseif ($event->portal_name == 'customer-app' || $event->portal_name == 'customer-portal') {
            $event->created_by_type = "App\\Models\\Customer";
            if ($user_id) {
                $event->created_by = $user_id;
                $event->created_by_name = "Customer - " . (Customer::find($user_id))->profile->name;
            }
        }
        $event->created_at = Carbon::now();
        $event->save();
    }
}