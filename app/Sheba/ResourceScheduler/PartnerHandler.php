<?php namespace Sheba\ResourceScheduler;

use App\Models\Category;
use App\Models\Partner;
use App\Models\ResourceSchedule;
use Carbon\Carbon;

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
        $category = $category instanceof Category ? $category : Category::find($category);

//        $this->partner->resourcesInCategory($category)->each(function ($resource) use ($date, $time, &$is_available, &$available_resources, &$unavailable_resources, $category) {
//            if (scheduler($resource)->isAvailableForCategory($date, $time, $category)) {
//                $available_resources->push($resource);
//                $is_available = true;
//            } else {
//                $unavailable_resources->push($resource);
//            }
//        });
//        return collect([
//            'is_available' => $is_available,
//            'available_resources' => $available_resources,
//            'unavailable_resources' => $unavailable_resources
//        ]);

        $resource_id = $this->partner->resourcesInCategory($category)->pluck('id')->toArray();
        $start_time = Carbon::parse($date . ' ' . $time);
        $end_time = Carbon::parse($date . ' ' . $time)->addMinutes($category->book_resource_minutes);

        $booked_resources = ResourceSchedule::startBetween($start_time, $end_time)->orWhere(function ($q) use ($start_time, $end_time) {
            $q->endBetween($start_time, $end_time);
        })->orWhere(function ($q) use ($start_time) {
            $q->byDateTime($start_time);
        })->orWhere(function ($q) use ($end_time) {
            $q->byDateTime($end_time);
        })->orWhere(function ($q) use ($start_time, $end_time) {
            $q->startAndEndAt($start_time, $end_time);
        })->whereIn('resource_id', $resource_id)->select('id', 'resource_id', 'job_id', 'start', 'end')->get();

        return collect([
            'is_available' => $resource_id > $booked_resources->count() ? 1 : 0
        ]);
    }
}