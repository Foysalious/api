<?php namespace App\Transformers\Business;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ShiftAssignment\ShiftAssignment;

class EmployeeShiftDetailsTransformer extends TransformerAbstract
{
    public function transform(ShiftAssignment $shiftAssignement)
    {
        $shift_details = [
            'id'                        => $shiftAssignement->id,
            'business_member_id'        => $shiftAssignement->business_member_id,
            'shift_id'                  => $shiftAssignement->shift_id,
            'shift_name'                => $shiftAssignement->shift_name,
            'date'                      => Carbon::parse($shiftAssignement->date)->format('d-m-Y'),
            'start_time'                => Carbon::parse($shiftAssignement->start_time)->format('h:i A'),
            'end_time'                  => Carbon::parse($shiftAssignement->end_time)->format('h:i A'),
            'is_half_day'               => $shiftAssignement->is_half_day,
            'color_code'                => $shiftAssignement->color_code,
            'is_general'                => $shiftAssignement->is_general,
            'is_unassigned'             => $shiftAssignement->is_unassigned,
            'is_shift'                  => $shiftAssignement->is_shift,
            'shift_settings'            => $shiftAssignement->shift_settings,
            'created_by'                => $shiftAssignement->created_by,
            'created_by_name'           => $shiftAssignement->created_by_name,
            'updated_by'                => $shiftAssignement->updated_by,
            'updated_by_name'           => $shiftAssignement->updated_by_name
        ];
        if($shiftAssignement->is_shift) $shift_details['title'] = $shiftAssignement->shift->title;
        return $shift_details;
    }
}
