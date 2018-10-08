<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Partner;
use App\Models\PartnerWorkingHour;
use App\Models\ResourceSchedule;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class ScheduleTimeController extends Controller
{
    const SCHEDULE_START = '09:00:00';
    const SCHEDULE_END = '21:00:00';

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app',
                'category' => 'sometimes|required|numeric',
                'partner' => 'sometimes|required|numeric'
            ]);
            if ($request->has('category') && $request->has('partner')) {
                $partner = Partner::find(233);
                $category = Category::find($request->category);
                $sheba_slots = $this->getPartnerSlots($partner, $category);
                return api_response($request, $sheba_slots, 200, ['dates' => $sheba_slots]);
            }
            $slots = ScheduleSlot::where([['start', '>=', DB::raw("CAST('" . self::SCHEDULE_START . "' As time)")], ['end', '<=', DB::raw("CAST('" . self::SCHEDULE_END . "' As time)")]])->get();
            $current_time = Carbon::now();
            if ($request->has('category')) {
                $current_time->addMinutes($this->getPreparationTime($request->category));
            }
            $time_slots = $valid_time_slots = $sheba_slots = [];
            foreach ($slots as $slot) {
                $slot_start_time = Carbon::parse($slot->start);
                $slot_end_time = Carbon::parse($slot->end);
                $time_slot_key = $slot->start . '-' . $slot->end;
                $time_slot_value = $slot_start_time->format('g:i A') . '-' . $slot_end_time->format('g:i A');
                if ($slot_start_time > $current_time) {
                    $valid_time_slots[$time_slot_key] = $time_slot_value;
                    $isValid = 1;
                } else {
                    $isValid = 0;
                }
                array_push($sheba_slots, array(
                    'key' => $time_slot_key,
                    'value' => $time_slot_value,
                    'isValid' => $isValid
                ));
                $time_slots[$time_slot_key] = $time_slot_value;
            }
            if ($request->has('for')) {
                return api_response($request, $sheba_slots, 200, ['times' => $sheba_slots]);
            } else {
                return api_response($request, $sheba_slots, 200, ['times' => $time_slots, 'valid_times' => $valid_time_slots]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getPreparationTime($category)
    {
        $category_model = Category::where('id', (int)$category)->first();
        if (!$category_model) $category_model = Category::where('slug', $category)->first();
        return $category_model->preparation_time_minutes;
    }

    public function getPartnerSlots(Partner $partner, $category, $limit = 14)
    {
        try {
            $final = [];
            $today = Carbon::now();
            $slots = $this->getAllSlots();
            $last_day = $today->copy()->addDays($limit);
            $resource_ids = $partner->resourcesInCategory($category->id)->pluck('id')->unique()->toArray();
            $total_resources = count($resource_ids);
            $schedule_slots = ResourceSchedule::whereIn('resource_id', $resource_ids)
                ->select('id', 'start', 'end', 'resource_id', DB::raw('Date(start) as schedule_date'))
                ->where('start', '>=', $today->toDateTimeString())
                ->where('end', '<=', $last_day->format('Y-m-d') . ' ' . $slots[11]->end)
                ->get();
            $schedule_slots_by_date = $schedule_slots->groupBy('schedule_date');
            while ($today <= $last_day) {
                $date = $today->toDateString();
                if ($working_day = $this->worksAtThisDay($partner, $today)) {
                    $slots = $this->getSlotsByDay($slots, $today, $working_day);
                    $schedule_slots = $schedule_slots_by_date->get($date);
                    if (!$schedule_slots) {
                        array_push($final, ['value' => $date, 'slots' => $slots->toArray()]);
                    } else {
                        foreach ($slots as $slot) {
                            $start_time = Carbon::parse($date . ' ' . $slot->start);
                            $end_time = Carbon::parse($date . ' ' . $slot->end)->addMinutes($category->book_resource_minutes);
                            $booked_resources = collect();
                            foreach ($schedule_slots as $schedule_slot) {
                                if ($schedule_slot->start->gte($start_time) || $schedule_slot->end->lte($end_time)) $booked_resources->push($schedule_slot->resource_id);
                            }
                            $slot->is_available = $total_resources > $booked_resources->unique()->count() ? 1 : 0;
                        }

                        array_push($final, ['value' => $date, 'slots' => $slots->toArray()]);
                    }
                } else {
                    array_push($final, ['value' => $date, 'slots' => $slots->map(function ($slot) {
                        $slot['is_available'] = 0;
                        return $slot;
                    })->toArray()]);
                }
                $today->addDay(1);
            }
            return $final;
        } catch (\Throwable $e) {
            dd($e);
        }

    }

    private function getAllSlots()
    {
        return ScheduleSlot::select('start', 'end')
            ->where([['start', '>=', DB::raw("CAST('" . self::SCHEDULE_START . "' As time)")], ['end', '<=', DB::raw("CAST('" . self::SCHEDULE_END . "' As time)")]])->get();
    }

    private function worksAtThisDay(Partner $partner, $today)
    {
        return $partner->workingHours->where('day', $today->format('l'))->first();
    }

    private function getSlotsByDay($slots, Carbon $date, PartnerWorkingHour $working_day)
    {
        $date_string = $date->toDateString();
        $working_day_start_time = Carbon::parse($date_string . ' ' . $working_day->start_time);
        $working_day_end_time = Carbon::parse($date_string . ' ' . $working_day->end_time);
        $isToday = $date->isToday();
        foreach ($slots as $slot) {
            $slot_start_time = Carbon::parse($date->toDateString() . ' ' . $slot->start);
            if ($isToday && ($slot_start_time < $date)) {
                $slot->is_available = 0;
            } else {
                $slot->is_available = $slot_start_time->gte($working_day_start_time) && $slot_start_time->lte($working_day_end_time) ? 1 : 0;
            }
        }
        return $slots;
    }

}