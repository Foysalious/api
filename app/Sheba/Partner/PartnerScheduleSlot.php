<?php

namespace Sheba\Partner;

use App\Models\Category;
use App\Models\Partner;
use App\Models\ResourceSchedule;
use App\Models\ScheduleSlot;
use Carbon\Carbon;

class PartnerScheduleSlot
{
    /** @var Partner */
    private $partner;
    /** @var Category */
    private $category;
    /** @var Carbon */
    private $today;

    const SCHEDULE_START = '09:00:00';
    const SCHEDULE_END = '21:00:00';
    private $shebaSlots;
    private $resources;

    public function __construct()
    {
        $this->shebaSlots = $this->getShebaSlots();
        $this->today = Carbon::today();
    }

    private function getShebaSlots()
    {
        return ScheduleSlot::select('start', 'end')
            ->where([
                ['start', '>=', DB::raw("CAST('" . self::SCHEDULE_START . "' As time)")],
                ['end', '<=', DB::raw("CAST('" . self::SCHEDULE_END . "' As time)")]
            ])->get();
    }

    public function setPartner($partner)
    {
        $this->partner = ($partner instanceof Partner) ? $partner : Partner::find($partner);
    }

    public function setCategory($category)
    {
        $this->category = ($category instanceof Category) ? $category : Category::find($category);
    }

    public function get($for_days = 14)
    {
        $this->resources = $this->getResources();
        $last_day = $this->today->copy()->addDays($for_days);
        $booked_schedules = $this->getBookedSchedules($this->today->toDateTimeString() . ' 00:00:00', $last_day->format('Y-m-d') . ' 23:59:59');
        $booked_schedules_group_by_date = $booked_schedules->groupBy('schedule_date');
        for ($i = 0; $i < 14; $i++) {
            $this->addAvailabilityToSlots();
        }
    }

    private function getResources()
    {
        return isset($this->category) ? $this->partner->resourcesInCategory($this->category->id) : $this->partner->handymanResources;
    }

    private function getBookedSchedules($start, $end)
    {
        return ResourceSchedule::whereIn('resource_id', $this->resources->pluck('id')->unique()->toArray())
            ->select('id', 'start', 'end', 'resource_id', DB::raw('Date(start) as schedule_date'))
            ->where('start', '>=', $start)
            ->where('end', '<=', $end)
            ->get();
    }

    private function addAvailabilityToSlots()
    {

    }
}