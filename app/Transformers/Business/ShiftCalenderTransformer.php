<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;

class ShiftCalenderTransformer
{
    public function transform($shift_calenders)
    {
        $data = $header = [];
        foreach ($shift_calenders as $shift_calender)
        {
            $header[$shift_calender->date] =
            [
                'date_raw' => $shift_calender->date,
                'date' =>  Carbon::parse($shift_calender->date)->format('d M'),
                'day' => Carbon::parse($shift_calender->date)->format('l')
            ];
            if (!isset($data[$shift_calender->business_member_id]['employee'])){
                $business_member = BusinessMember::find($shift_calender->business_member_id);
                $data[$shift_calender->business_member_id]['employee'] = [
                    'business_member_id' => $shift_calender->business_member_id,
                    'employee_id' => $business_member->employee_id,
                    'name' => $business_member->member->profile->name
                ];
            }

            $data[$shift_calender->business_member_id]['date'][$shift_calender->date] = [
                'id' => $shift_calender->id,
                'date' => $shift_calender->date,
                'business_member_id' => $shift_calender->business_member_id,
                'is_general' => $shift_calender->is_general,
                'is_unassigned' => $shift_calender->is_unassigned,
                'is_shift' => $shift_calender->is_shift,
                'shift_name' => $shift_calender->shift_name
            ];
            if (!isset($data[$shift_calender->business_member_id]['display_priority'])) $data[$shift_calender->business_member_id]['display_priority'] = $shift_calender->is_shift == 1 ? 0 : 1;
            else
                if ($shift_calender->is_shift) $data[$shift_calender->business_member_id]['display_priority'] = $data[$shift_calender->business_member_id]['display_priority'] == 0 ? 0 : 1;
        }
        return ['header' => $header, 'data' => $data];
    }
}
