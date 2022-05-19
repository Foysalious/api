<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ShiftCalender\ShiftCalender;

class EmployeeShiftDetailsTransformer extends TransformerAbstract
{
    public function transform(ShiftCalender $shiftCalender)
    {
        return [
            'id'                        => $shiftCalender->id,
            'business_member_id'        => $shiftCalender->business_member_id,
            'shift_id'                  => $shiftCalender->shift_id,
            'title'                     => $shiftCalender->shift->title,
            'shift_name'                => $shiftCalender->shift_name,
            'date'                      => Carbon::parse($shiftCalender->date)->format('d-m-Y'),
            'start_time'                => Carbon::parse($shiftCalender->start_time)->format('h:i A'),
            'end_time'                  => Carbon::parse($shiftCalender->end_time)->format('h:i A'),
            'is_half_day'               => $shiftCalender->is_half_day,
            'color_code'                => $shiftCalender->color_code,
            'is_general'                => $shiftCalender->is_general,
            'is_unassigned'             => $shiftCalender->is_unassigned,
            'is_shift'                  => $shiftCalender->is_shift,
            'shift_settings'            => $shiftCalender->shift_settings,
            'created_by'                => $shiftCalender->created_by,
            'created_by_name'           => $shiftCalender->created_by_name,
            'updated_by'                => $shiftCalender->updated_by,
            'updated_by_name'           => $shiftCalender->updated_by_name

        ];
    }
}
