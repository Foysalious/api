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
use Sheba\Partner\PartnerScheduleSlot;

class ScheduleTimeController extends Controller
{
    const SCHEDULE_START = '09:00:00';
    const SCHEDULE_END = '21:00:00';

    public function index(Request $request, PartnerScheduleSlot $partnerSchedule)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app',
                'category' => 'sometimes|required|numeric',
                'partner' => 'sometimes|required|numeric',
                'limit' => 'sometimes|required|numeric:min:1'
            ]);
            if ($request->has('category') && $request->has('partner')) {
                $dates = $partnerSchedule->setPartner($request->partner)->setCategory($request->category)->get($request->limit);
                return api_response($request, $dates, 200, ['dates' => $dates]);
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
}