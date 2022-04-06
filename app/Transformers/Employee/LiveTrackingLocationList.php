<?php namespace App\Transformers\Employee;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\TrackingLocation;

class LiveTrackingLocationList extends TransformerAbstract
{
    public function transform(TrackingLocation $tracking_location)
    {
        $location = $tracking_location->location;
        return [
            'business_id' => $tracking_location->business_id,
            'business_member_id' => $tracking_location->business_member_id,
            'time' => Carbon::parse($tracking_location->time)->format('h:i A'),
            'location' => [
                'lat' => $location->lat,
                'lng' => $location->lng,
                'address' => $location->address,
            ]
        ];
    }
}