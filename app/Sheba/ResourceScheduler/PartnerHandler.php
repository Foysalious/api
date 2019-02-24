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


        $resource_ids = $this->partner->resourcesInCategory($category)->pluck('id')->unique()->toArray();
        $dates = !is_array($date) ? [$date] : $date;
        $booked_schedules = collect();
        foreach ($dates as $date) {
            $start_time = Carbon::parse($date . ' ' . $time);
            $end_time = Carbon::parse($date . ' ' . $time)->addMinutes($category->book_resource_minutes);
            $booked = ResourceSchedule::whereIn('resource_id', $resource_ids)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->where([['start', '>', $start_time], ['start', '<', $end_time]]);
                    $query->orwhere([['end', '>', $start_time], ['end', '<', $end_time]]);
                    $query->orwhere([['start', '<', $start_time], ['end', '>', $start_time]]);
                    $query->orwhere([['start', '<', $end_time], ['end', '>', $end_time]]);
                    $query->orwhere([['start', $start_time], ['end', $end_time]]);
                })->get();
            $booked_schedules = $booked_schedules->merge($booked);
        }
        return collect([
            'is_available' => count($resource_ids) > $booked_schedules->pluck('resource_id')->unique()->count() ? 1 : 0
        ]);
    }
}