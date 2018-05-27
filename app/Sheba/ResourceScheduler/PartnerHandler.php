<?php namespace Sheba\ResourceScheduler;

use App\Models\Partner;

class PartnerHandler
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function isAvailable($date, $time, $category)
    {
        $is_available = false;
        $available_resources = collect([]);
        $unavailable_resources = collect([]);
        $rent_car_ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        if (!in_array($category, $rent_car_ids)) {
            $this->partner->resourcesInCategory($category)->each(function ($resource) use ($date, $time, &$is_available, &$available_resources, &$unavailable_resources) {
                if (scheduler($resource)->isAvailable($date, $time)) {
                    $available_resources->push($resource);
                    $is_available = true;
                } else {
                    $unavailable_resources->push($resource);
                }
            });
        } else {
            $is_available = 1;
        }

        return collect([
            'is_available' => $is_available,
            'available_resources' => $available_resources,
            'unavailable_resources' => $unavailable_resources
        ]);
    }
}