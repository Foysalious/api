<?php

namespace Sheba\Partner;

use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryScheduleSlot\CategoryScheduleSlot;
use App\Models\Partner;
use App\Models\ResourceSchedule;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;

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
    private $preparationTime;
    private $portalName;
    private $whitelistedPortals;
    private $for;
    private $errorMessage;
    private $limit;

    public function __construct()
    {
        $this->portalName = request()->header('portal-name');
        $this->whitelistedPortals = ['manager-app', 'manager-web', 'admin-portal'];
        $this->today = Carbon::now()->addMinutes(15);
    }


    public function getErrorMessage()
    {
        return $this->errorMessage;
    }


    private function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    private function getShebaSlots()
    {
        $end_time = self::SCHEDULE_END;
        if ($this->isWhitelistedPortal()) $end_time = '24:00:00';
        return ScheduleSlot::select('start', 'end')
            ->where([
                ['start', '>=', DB::raw("CAST('" . self::SCHEDULE_START . "' As time)")],
                ['end', '<=', DB::raw("CAST('" . $end_time . "' As time)")]
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

    public function setFor($for)
    {
        $this->for = $for;
        return $this;
    }

    public function get($for_days = 14)
    {
        $final = [];
        $category_partner = $this->partner->categories->where('id', $this->category->id)->first();
        if (!$category_partner) {
            $this->setErrorMessage('Partner #' . $this->partner->id . ' doesn\'t serve category #' . $this->category->id);
            return null;
        }
        $this->limit = $for_days;
        $last_day = $this->today->copy()->addDays($for_days);
        $day = $this->today->copy();
        while ($day < $last_day) {
            $slot = $this->formatSlots($day);
            if($slot) {
                array_push($final, ['value' => $day->toDateString(), 'slots' => $slot]);
            }
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
        $leaves = $this->partner->leaves()->select('id', 'partner_id', 'start', 'end')
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start', [$start, $end]);
                })->orWhere(function ($q) use ($start, $end) {
                    $q->whereBetween('end', [$start, $end]);
                })->orWhere('end', null);
            })->get();
        return $leaves->count() > 0 ? $leaves : null;
    }

    private function addAvailabilityToShebaSlots(Carbon $day)
    {
        $this->addAvailabilityByWorkingInformation($day);
        $this->addAvailabilityByPreparationTime($day);
        $this->addAvailabilityByResource($day);
    }

    private function addAvailabilityByWorkingInformation(Carbon $day)
    {
        if ($this->isWhitelistedPortal()) {
            $this->shebaSlots->each(function ($slot) {
                $slot['is_available'] = 1;
            });
        } elseif ($working_day = $this->getWorkingDay($day)) {
            $date_string = $day->toDateString();
            $working_hour_start_time = Carbon::parse($date_string . ' ' . $working_day->start_time);
            $working_hour_end_time = Carbon::parse($date_string . ' ' . $working_day->end_time);
            $isToday = $day->isToday();
            foreach ($this->shebaSlots as $slot) {
                $slot_start_time = Carbon::parse($date_string . ' ' . $slot->start);
                if (!($slot_start_time->gte($working_hour_start_time) && $slot_start_time->lte($working_hour_end_time)) || $this->isBetweenAnyLeave($slot_start_time) || ($isToday && ($slot_start_time < $day))) {
                    $slot['is_available'] = 0;
                } else {
                    $is_available = ($working_hour_end_time->notEqualTo($slot_start_time) && $slot_start_time->between($working_hour_start_time, $working_hour_end_time, true));
                    $slot['is_available'] = $is_available ? 1 : 0;
                }
            }
        } else {
            $this->shebaSlots->each(function ($slot) {
                $slot['is_available'] = 0;
            });
        }
    }

    private function isWhitelistedPortal()
    {
        return in_array($this->portalName, $this->whitelistedPortals) && $this->for != 'eshop';
    }

    private function getWorkingDay(Carbon $day)
    {
        return $this->partner->workingHours->where('day', $day->format('l'))->first();
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

    private function addAvailabilityByPreparationTime(Carbon $day)
    {
        if ($this->preparationTime > 0) {
            $date_string = $day->toDateString();
            $this->shebaSlots->each(function ($slot) use ($date_string) {
                if ($slot->is_available) {
                    $start_time = Carbon::parse($date_string . ' ' . $slot->start);
                    $end_time = Carbon::parse($date_string . ' ' . $slot->end);
                    $preparation_time = Carbon::createFromTime(Carbon::now()->hour)->addMinute(61)->addMinute($this->preparationTime);
                    $slot['is_available'] = $preparation_time->lte($start_time) || $preparation_time->between($start_time, $end_time) ? 1 : 0;
                }
            });
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
                $end_time = Carbon::parse($date_string . ' ' . $slot->start)->addMinutes($this->category->book_resource_minutes);
                $booked_resources = collect();
                foreach ($bookedSchedules as $booked_schedule) {
                    if ($this->hasBookedSchedule($booked_schedule, $start_time, $end_time)) $booked_resources->push($booked_schedule->resource_id);
                }
                $is_available = (int)$total_resources > $booked_resources->unique()->count();
                $slot['is_available'] = $is_available ? 1 : 0;
            }
        }
    }

    public function getSlots($day)
    {
        $last_day = $this->today->copy()->addDays($this->limit);
        if($this->category) {
            $slots = CategoryScheduleSlot::category($this->category->id)->day($day->dayOfWeek)->get();
            $slots = $slots->map(function ($slot) {
                return $slot->scheduleSlot  ;
            });
        }
        else $slots = $this->getShebaSlots();
        $this->shebaSlots = $slots;
        if(!$this->shebaSlots->first()) return null;
        $start = $this->today->toDateString() . ' ' . $this->shebaSlots->first()->start;
        $end = $last_day->format('Y-m-d') . ' ' . $this->shebaSlots->last()->end;

        if ($this->partner) {
            $this->resources = $this->getResources();
            $this->bookedSchedules = $this->getBookedSchedules($start, $end);
            $this->runningLeaves = $this->getLeavesBetween($start, $end);
            $category_partner = $this->partner->categories->where('id', $this->category->id)->first();
            if ($this->category && $category_partner) $this->preparationTime = $category_partner->pivot->preparation_time_minutes;
        }

        return $this->shebaSlots;
    }

    private function formatSlots(Carbon $day)
    {
        $current_time = $this->today->copy();

        $slots = $this->getSlots($day);

        if ($this->partner) $current_time = $this->today->copy()->addMinutes($this->preparationTime);
        else if($this->category) $current_time = $this->today->copy()->addMinutes($this->category->preparation_time_minutes);

        if(!$slots) return null;
        $this->addAvailabilityToShebaSlots($day);
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

    private function hasBookedSchedule($booked_schedule, $start_time, $end_time)
    {
        return $booked_schedule->start->gt($start_time) && $booked_schedule->start->lt($end_time) ||
            $booked_schedule->end->gt($start_time) && $booked_schedule->end->lt($end_time) ||
            $booked_schedule->start->lt($start_time) && $booked_schedule->end->gt($start_time) ||
            $booked_schedule->start->lt($end_time) && $booked_schedule->end->gt($end_time) ||
            $booked_schedule->start->eq($start_time) && $booked_schedule->end->eq($end_time);
    }
}
