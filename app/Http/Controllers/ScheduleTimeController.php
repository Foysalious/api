<?php

namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\Partner;
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
                'for' => 'sometimes|required|string|in:app,eshop',
                'category' => 'sometimes|required|numeric|min:1',
                'partner' => 'sometimes|required|numeric|min:1',
                'limit' => 'sometimes|required|numeric|min:1'
            ]);
            if ($request->filled('category') && $request->filled('partner')) {
                $partnerSchedule->setPartner($request->partner)->setCategory($request->category)->setFor($request->for);
                $dates = $request->filled('limit') ? $partnerSchedule->get($request->limit) : $partnerSchedule->get();
                return $dates ? api_response($request, $dates, 200, ['dates' => $dates]) : api_response($request, null, 400, [
                    'message' => $partnerSchedule->getErrorMessage()
                ]);
            }
            $slots = ScheduleSlot::where([['start', '>=', DB::raw("CAST('" . self::SCHEDULE_START . "' As time)")], ['end', '<=', DB::raw("CAST('" . self::SCHEDULE_END . "' As time)")]])->get();
            $current_time = Carbon::now();
            if ($request->filled('category')) {
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
            if ($request->filled('for')) {
                return api_response($request, $sheba_slots, 200, ['times' => $sheba_slots]);
            } else {
                return api_response($request, $sheba_slots, 200, ['times' => $time_slots, 'valid_times' => $valid_time_slots]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
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