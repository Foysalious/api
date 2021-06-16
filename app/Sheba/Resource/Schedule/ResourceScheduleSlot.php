<?php namespace Sheba\Resource\Schedule;

use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryScheduleSlot\CategoryScheduleSlot;
use App\Models\Job;
use App\Models\Partner;
use App\Models\ResourceSchedule;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use DB;

class ResourceScheduleSlot
{
    /** @var Category */
    private $category;
    /** @var Partner */
    private $partner;
    private $limit;

    /** @var Carbon */
    private $today;
    /** @var Collection */
    private $shebaSlots;
    /** @var Collection */
    private $bookedSchedules;
    /** @var Collection */
    private $runningLeaves;

    private $scheduleStart;
    private $scheduleEnd;

    private $resources;
    private $preparationTime;
    private $portal;
    private $resource;

    public function __construct()
    {
        $this->limit = 1;
        $this->scheduleStart = '09:00:00';
        $this->scheduleEnd = '21:00:00';
        $this->preparationTime = 0;
        $this->today = Carbon::now()->addMinutes(15);
    }

    private function getShebaSlots()
    {
        if ($this->portal && $this->portal == 'manager') $this->scheduleEnd = '24:00:00';
        return ScheduleSlot::select('start', 'end')->where([
            ['start', '>=', DB::raw("CAST('" . $this->scheduleStart . "' As time)")], ['end', '<=', DB::raw("CAST('" . $this->scheduleEnd . "' As time)")]
        ])->get();
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;

    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;

    }

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setPortal($portal)
    {
        $this->portal = $portal;
        return $this;
    }

    private function getBookedSchedules($start, $end)
    {
        return ResourceSchedule::whereIn('resource_id', $this->resources->pluck('id')->unique()->toArray())->select('start', 'end', 'resource_id', 'job_id', DB::raw('Date(start) as schedule_date'))->where('start', '>=', $start)->where('end', '<=', $end)->get();
    }

    private function getLeavesBetween($start, $end)
    {
        $leaves = $this->partner->leaves()->select('id', 'partner_id', 'start', 'end')->where(function ($q) use ($start, $end) {
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
        $this->addAvailabilityByResource($day);
        $this->hasAvailableBookingTime($day);
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
                if (!($slot_start_time->gte($working_hour_start_time) && $slot_start_time->lte($working_hour_end_time)) || $this->isBetweenAnyLeave($slot_start_time) || ($isToday && ($slot_start_time < $day))) {
                    $slot['is_available'] = 0;
                    $slot['unavailability_reason'] = "working hour";
                    $slot['message'] = 'আপনি যেই সময়টি সিলেক্ট করেছেন সেটি আপনার কাজের সময় এর সাথে মিলছে না';
                } else {
                    $is_available = ($working_hour_end_time->notEqualTo($slot_start_time) && $slot_start_time->between($working_hour_start_time, $working_hour_end_time, true));
                    $slot['is_available'] = $is_available ? 1 : 0;
                    $slot['unavailability_reason'] = $slot['is_available'] ? null : "working hour";
                    $slot['message'] = $slot['is_available'] ? null : 'আপনি যেই সময়টি সিলেক্ট করেছেন সেটি আপনার কাজের সময় এর সাথে মিলছে না';
                }
            }
        } else {
            $this->shebaSlots->each(function ($slot) {
                $slot['is_available'] = 0;
                $slot['unavailability_reason'] = "working day";
                $slot['message'] = 'আপনি যেই সময়টি সিলেক্ট করেছেন সেটি আপনার কাজের সময় এর সাথে মিলছে না';
            });
        }
    }

    private function getWorkingDay(Carbon $day)
    {
        return $this->partner->workingHours->where('day', $day->format('l'))->first();
    }

    private function isBetweenAnyLeave(Carbon $time)
    {
        if (!$this->runningLeaves) return false; else {
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
            foreach ($this->shebaSlots as $slot) {
                if (!$slot['is_available']) continue;
                $start_time = Carbon::parse($date_string . ' ' . $slot->start);
                $end_time = Carbon::parse($date_string . ' ' . $slot->start)->addMinutes($this->category->book_resource_minutes);
                foreach ($bookedSchedules as $booked_schedule) {
                    if ($this->hasBookedSchedule($booked_schedule, $start_time, $end_time)) {
                        $slot['is_available'] = 0;
                        $slot['unavailability_reason'] = "booked schedule";
                        $job = Job::find($booked_schedule->job_id);
                        $slot['message'] = 'আপনাকে ' . $booked_schedule->start->format('g:i A').'-'.$booked_schedule->end->format('g:i A') . ' পর্যন্ত আরেকটি কাজে (অর্ডার আইডি ' . $job->partnerOrder->order->code() .') অ্যাসাইন করা আছে, সে জন্যে ' . $start_time->format('g:i A') . '-' . $end_time->format('g:i A') . ' পর্যন্ত আপনি অন্য কোন কাজ করতে পারবেন না।';
                    }
                    else {
                        $slot['is_available'] = $slot->is_available;
                        $slot['unavailability_reason'] = $slot['is_available'] ? null : $slot->unavailability_reason;
                        $slot['message'] = $slot['is_available'] ? null : $slot->message;
                    }
                }
            }
        }
    }

    private function hasBookedSchedule($booked_schedule, $start_time, $end_time)
    {
        return $booked_schedule->start->gt($start_time) && $booked_schedule->start->lt($end_time) || $booked_schedule->end->gt($start_time) && $booked_schedule->end->lt($end_time) || $booked_schedule->start->lt($start_time) && $booked_schedule->end->gt($start_time) || $booked_schedule->start->lt($end_time) && $booked_schedule->end->gt($end_time) || $booked_schedule->start->eq($start_time) && $booked_schedule->end->eq($end_time);
    }

    private function hasAvailableBookingTime(Carbon $day)
    {
        $date_string = $day->toDateString();
        foreach ($this->shebaSlots as $key => $slot) {
            $prevSlot = $this->shebaSlots->has($key-1) ? $this->shebaSlots[$key-1] : null;
            $nextSlot = $this->shebaSlots->has($key+1) ? $this->shebaSlots[$key+1] : null;
            if ($slot->is_available && $nextSlot && !$nextSlot->is_available && $prevSlot && !$prevSlot->is_available) {
                $start_time = Carbon::parse($date_string . ' ' . $slot->start);
                $end_time = Carbon::parse($date_string . ' ' . $slot->end);
                $slot['is_available'] = $start_time->diffInMinutes($end_time) >= $this->category->book_resource_minutes ? 1 : 0;
                $slot['unavailability_reason'] = $slot['is_available'] ? $slot->unavailability_reason : 'booking time';
                $slot['message'] = $slot['is_available'] ? null : 'এই সময়ে কাজটি শেষ করার জন্য আপনার কাছে পর্যাপ্ত সময় নেই';
            }
        }
    }

    public function getSlots($day)
    {
        $last_day = $this->today->copy()->addDays($this->limit);
        if($this->category) {
            $slots = CategoryScheduleSlot::category($this->category->id)->day($day->dayOfWeek)->get();
            $slots = $slots->map(function ($slot) {
                return $slot->scheduleSlot;
            });
        }
        else $slots = $this->getShebaSlots();
        $this->shebaSlots = $slots;
        if(!$this->shebaSlots->first()) return null;
        $start = $this->today->toDateString() . ' ' . $this->shebaSlots->first()->start;
        $end = $last_day->format('Y-m-d') . ' ' . $this->shebaSlots->last()->end;

        if ($this->partner) {
            $this->resources = collect([$this->resource]);
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
        if(!$slots) return null;
        if ($this->partner) {
            $this->addAvailabilityToShebaSlots($day);
        }
        if ($this->partner) $current_time = $this->today->copy()->addMinutes($this->preparationTime);
        else if($this->category) $current_time = $this->today->copy()->addMinutes($this->category->preparation_time_minutes);

        foreach ($slots as &$slot) {
            $slot['key'] = $slot['start'] . '-' . $slot['end'];
            $start = Carbon::parse($day->toDateString() . ' ' . $slot['start']);
            $end = Carbon::parse($day->toDateString() . ' ' . $slot['end']);
            $slot['value'] = $start->format('g') . ' - ' . $end->format('g A');
            $slot_start = humanReadableShebaTime($slot['start'], true);
            $slot_end = humanReadableShebaTime($slot['end'], true);
            $slot['start'] = $slot_start;
            $slot['end'] = $slot_end;
            $slot['is_valid'] = $start > $current_time ? 1 : 0;
            $slot['is_available'] = isset($slot['is_available']) ? $slot['is_available'] : $slot['is_valid'];
            $slot['unavailability_reason'] = $slot['is_valid'] ? $slot['unavailability_reason'] : null;
            $slot['message'] = $slot['is_valid'] ? $slot['message'] : null;
        }
        return $slots;
    }

    public function getSchedulesByResource($resource)
    {
        $final = [];
        $last_day = $this->today->copy()->addDays($this->limit);
        $this->resource = $resource;

        $day = $this->today->copy();
        while ($day < $last_day) {
            $slot = $this->formatSlots($day);
            if($slot) {
                array_push($final, [
                    'value' => $day->toDateString(),
                    'slots' => $slot
                ]);
            }
            $day->addDay();
        }
        return $final;
    }
}
