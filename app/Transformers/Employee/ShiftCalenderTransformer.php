<?php namespace App\Transformers\Employee;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ShiftCalenderTransformer extends TransformerAbstract
{
    public function transform($shift_calender)
    {
        return [
            'id' => $shift_calender->id,
            'date' => $shift_calender->date,
            'is_general' => $shift_calender->is_general,
            'is_unassigned' => $shift_calender->is_unassigned,
            'is_shift' => $shift_calender->is_shift,
            'shift_name' => $shift_calender->shift_name,
            'shift_start' => Carbon::parse($shift_calender->start_time)->format('h:i A'),
            'shift_end' => Carbon::parse($shift_calender->end_time)->format('h:i A'),
        ];
    }
}