<?php

namespace App\Http\Controllers;


use App\Models\Category;
use App\Models\ScheduleSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class ScheduleTimeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'for' => 'sometimes|required|string|in:app',
            ]);
            $slots = ScheduleSlot::where([
                ['start', '>=', DB::raw("CAST('09:00:00' As time)")],
                ['end', '<=', DB::raw("CAST('21:00:00' As time)")],
            ])->get();
            $current_time = Carbon::now();
            if ($request->has('category')) {
                $category = Category::where('id', (int)$request->category)->first();
                if (!$category) $category = Category::where('slug', $request->category)->first();
                $current_time = $current_time->addMinutes($category->preparation_time_minutes);
            }
            if ($request->has('for')) {
                $sheba_slots = $this->getShebaSlots($slots, $current_time);
                return api_response($request, $sheba_slots, 200, ['times' => $sheba_slots]);
            }
            $time_slots = $valid_time_slots = [];
            foreach ($slots as $slot) {
                $slot_start_time = Carbon::parse($slot->start);
                $slot_end_time = Carbon::parse($slot->end);
                $time_slot_key = $slot->start . '-' . $slot->end;
                $time_slot_value = $slot_start_time->format('g:i A') . '-' . $slot_end_time->format('g:i A');
                if ($slot_start_time > $current_time) {
                    $valid_time_slots[$time_slot_key] = $time_slot_value;
                }
                $time_slots[$time_slot_key] = $time_slot_value;
            }
            $result = ['times' => $time_slots, 'valid_times' => $valid_time_slots];
            return api_response($request, $result, 200, $result);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getShebaSlots($slots, $current_time)
    {
        $sheba_slots = [];
        foreach ($slots as $slot) {
            $slot_start_time = Carbon::parse($slot->start);
            $slot_end_time = Carbon::parse($slot->end);
            $isValid = 0;
            if ($slot_start_time > $current_time) {
                $isValid = 1;
            }
            array_push($sheba_slots, array(
                'key' => $slot->start . '-' . $slot->end,
                'value' => $slot_start_time->format('g:i A') . '-' . $slot_end_time->format('g:i A'),
                'isValid' => $isValid
            ));
        }
        return $sheba_slots;
    }
}