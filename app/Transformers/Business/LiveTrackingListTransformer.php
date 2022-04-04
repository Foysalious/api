<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class LiveTrackingListTransformer extends TransformerAbstract
{
    public function transform($tracking_locations)
    {
        return [
                'business_id' => $tracking_locations->business_id,
                'business_member_id' => $tracking_locations->business_member_id,
                'last_activity' => $tracking_locations->created_at->format('h:i A, j F'),
                'last_location_lat' => $tracking_locations->location->lat,
                'last_location_lng' => $tracking_locations->location->lng,
                'last_location_address' => $tracking_locations->location->address,
        ];
    }
}