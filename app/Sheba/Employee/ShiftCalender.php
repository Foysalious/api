<?php namespace App\Sheba\Employee;

use Carbon\Carbon;

class ShiftCalender
{
    private $shift_calender;
    private $business;

    public function __construct($business, $shift_calender)
    {
        $this->business = $business;
        $this->shift_calender = $shift_calender;
    }

    public function employee_shift_calender()
    {
        $shifts = $employee_shift_calender = [];
        $business_start_time = Carbon::parse($this->business->officeHour->start_time)->format('h:i A');
        $business_end_time = Carbon::parse($this->business->officeHour->end_time)->format('h:i A');
        foreach($this->shift_calender as $employeeShift)
        {
            $temp_calender =  [
                'id' => $employeeShift->id,
                'date' => $employeeShift->date,
                'is_general' => $employeeShift->is_general,
                'is_unassigned' => $employeeShift->is_unassigned,
                'is_shift' => $employeeShift->is_shift,
                'color_code' => $employeeShift->color_code
            ];

            if($employeeShift->is_general){
                $name = 'General';
                $temp_calender['shift_name'] = 'General';
                $temp_calender['shift_start'] = $business_start_time;
                $temp_calender['shift_end'] = $business_end_time;
                $shifts[] = $this->makeShiftData($employeeShift->id, $name, $employeeShift->color_code, $business_start_time, $business_end_time);
            }
            elseif($employeeShift->is_shift){
                $temp_calender['shift_name'] = $employeeShift->shift->title;
                $temp_calender['shift_start'] = Carbon::parse($employeeShift->start_time)->format('h:i A');
                $temp_calender['shift_end'] = Carbon::parse($employeeShift->end_time)->format('h:i A');
                $shifts[] = $this->makeShiftData($employeeShift->id, $employeeShift->shift->title, $employeeShift->color_code, $employeeShift->shift->start_time, $employeeShift->shift->end_time);
            }
            elseif($employeeShift->is_unassigned){
                $temp_calender['shift_name'] = 'Unassigned';
                $temp_calender['shift_start'] = null;
                $temp_calender['shift_end'] = null;
            }
            $employee_shift_calender[] = $temp_calender;
        }
        $shifts = collect($shifts)->unique('title')->values();
        return ['employee_shifts' =>$employee_shift_calender, 'shifts' => $shifts];
    }

    /**
     * @param $id
     * @param $name
     * @param $color_code
     * @param $start_time
     * @param $end_time
     * @return array
     */
    private function makeShiftData($id, $name, $color_code, $start_time, $end_time)
    {
        return [
            'id'            => $id,
            'title'         => $name,
            'color_code'    => $color_code,
            'start_time'    => $start_time,
            'end_time'      => $end_time
        ];
    }
}