<?php

namespace Sheba\Partner;

use App\Models\Category;
use App\Models\Partner;
use App\Models\ResourceSchedule;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use phpDocumentor\Reflection\Types\Integer;

class PartnerScheduleSlot
{
    /** @var Partner */
    private $partner;
    /** @var Category */
    private $category;
    /** @var Carbon */
    private $today;
    /** @var Collection */
    private $shebaSlots;
    /** @var Collection */
    private $bookedSchedules;
    /** @var Collection */
    private $runningLeaves;

    const SCHEDULE_START = '09:00:00';
    const SCHEDULE_END = '21:00:00';
    private $resources;

    public function __construct()
    {
        $this->shebaSlots = $this->getShebaSlots();
        $this->today = Carbon::now();
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
        return $this;
    }

    public function setCategory($category)
    {
        $this->category = ($category instanceof Category) ? $category : Category::find($category);
        return $this;
    }

    public function get($for_days = 14): array
    {
        $final = [];
        $last_day = $this->today->copy()->addDays($for_days);
        $start = $this->today->toDateString() . ' ' . $this->shebaSlots->first()->start;
        $end = $last_day->format('Y-m-d') . ' ' . $this->shebaSlots->last()->end;
        $this->resources = $this->getResources();
        $this->bookedSchedules = $this->getBookedSchedules($start, $end);
        $this->runningLeaves = $this->getLeavesBetween($start, $end);
        $day = $this->today->copy();
        while ($day <= $last_day) {
            $this->addAvailabilityToShebaSlots($day);
            array_push($final, ['value' => $day->toDateString(), 'slots' => $this->formatSlots($day, $this->shebaSlots->toArray())]);
            $day->addDay();
        }
        return $final;
    }

    private function getResources()
    {
        return isset($this->category) ? $this->partner->resourcesInCategory($this->category->id) : $this->partner->handymanResources;
    }

    private function getBookedSchedules($start, $end)
    {
        return ResourceSchedule::whereIn('resource_id', $this->resources->pluck('id')->unique()->toArray())
            ->select('start', 'end', 'resource_id', DB::raw('Date(start) as schedule_date'))
            ->where('start', '>=', $start)
            ->where('end', '<=', $end)
            ->get();
    }

    private function getLeavesBetween($start, $end)
    {
        $leaves = $this->partner->leaves()->select('id', 'partner_id', 'start', 'end')->where('start', '>=', $start)->Where(function ($q) use ($end) {
            $q->where('end', '<=', $end)->orWhere('end', null);
        })->get();
        return $leaves->count() > 0 ? $leaves : null;
    }

    private function addAvailabilityToShebaSlots(Carbon $day)
    {
        $this->addAvailabilityByWorkingInformation($day);
        $this->addAvailabilityByResource($day);
    }

    private function getWorkingDay(Carbon $day)
    {
        return $this->partner->workingHours->where('day', $day->format('l'))->first();
    }

    private function addAvailabilityByWorkingInformation(Carbon $day)
    {
        $working_day = $this->getWorkingDay($day);
        if ($working_day) {
            $date_string = $day->toDateString();
            $working_hour_start_time = Carbon::parse($date_string . ' ' . $working_day->start_time);
            $working_hour_end_time = Carbon::parse($date_string . ' ' . $working_day->end_time);
            $isToday = $day->isToday();
            foreach ($this->shebaSlots as $slot) {
                $slot_start_time = Carbon::parse($date_string . ' ' . $slot->start);
                if ($this->isBetweenAnyLeave($slot_start_time) || ($isToday && ($slot_start_time < $day))) {
                    $slot['is_available'] = 0;
                } else {
                    $is_available = (int)($working_hour_end_time->notEqualTo($slot_start_time) && $slot_start_time->between($working_hour_start_time, $working_hour_end_time, true));
                    $slot['is_available'] = $is_available ? 1 : 0;
                }
            }
        } else {
            $this->shebaSlots->each(function ($slot) {
                $slot['is_available'] = 0;
            });
        }
    }

    private function isBetweenAnyLeave(Carbon $time)
    {
        if (!$this->runningLeaves) return false;
        else {
            foreach ($this->runningLeaves as $runningLeave) {
                $start = $runningLeave->start;
                $end = $runningLeave->end;
                if ($end) {
                    if ($time->between($start, $end)) return true;
                } else {
                    if ($time->gte($start) && $end == null) return true;
                }
            }
            return false;
        }
    }

    private function addAvailabilityByResource(Carbon $day)
    {
        $booked_schedules_group_by_date = $this->bookedSchedules->groupBy('schedule_date');
        $date_string = $day->toDateString();
        if ($bookedSchedules = $booked_schedules_group_by_date->get($date_string)) {
            $total_resources = $this->resources->count();
            foreach ($this->shebaSlots as $slot) {
                if (!$slot['is_available']) continue;
                $start_time = Carbon::parse($date_string . ' ' . $slot->start);
                $end_time = Carbon::parse($date_string . ' ' . $slot->end)->addMinutes($this->category->book_resource_minutes);
                $booked_resources = collect();
                foreach ($bookedSchedules as $booked_schedule) {
                    if ($booked_schedule->start->gte($start_time) || $booked_schedule->end->lte($end_time)) $booked_resources->push($booked_schedule->resource_id);
                }
                $is_available = (int)$total_resources > $booked_resources->unique()->count();
                $slot['is_available'] = $is_available ? 1 : 0;
            }
        }
    }

    private function formatSlots(Carbon $day, $slots)
    {
        $current_time = Carbon::now();
        foreach ($slots as &$slot) {
            $slot['key'] = $slot['start'] . '-' . $slot['end'];
            $start = Carbon::parse($day->toDateString() . ' ' . $slot['start']);
            $end = Carbon::parse($day->toDateString() . ' ' . $slot['end']);
            $slot['value'] = $start->format('g') . ' - ' . $end->format('g A');
            $slot_start = humanReadableShebaTime($slot['start']);
            $slot_end = humanReadableShebaTime($slot['end']);
            $slot['start'] = $slot_start;
            $slot['end'] = $slot_end;
            $slot['is_valid'] = $start > $current_time ? 1 : 0;
        }
        return $slots;
    }
}