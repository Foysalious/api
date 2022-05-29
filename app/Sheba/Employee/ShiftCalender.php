<?php namespace App\Sheba\Employee;

use Sheba\Business\Attendance\CheckWeekend;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Carbon\Carbon;
use Sheba\Dal\BusinessWeekendSettings\BusinessWeekendSettingsRepo;

class ShiftCalender
{
    private $shift_calender;
    private $business;
    /* @var $businessWeekendRepo */
    private $businessWeekendRepo;
    /* @var $business_holiday_repo */
    private $business_holiday_repo;

    public function __construct($business, $shift_calender)
    {
        $this->business = $business;
        $this->shift_calender = $shift_calender;
        $this->businessWeekendRepo = app(BusinessWeekendSettingsRepo::class);
        $this->business_holiday_repo = app(BusinessHolidayRepoInterface::class);
    }

    public function employee_shift_calender()
    {
        $shifts = $employee_shift_calender = $data =[];
        $business_start_time = Carbon::parse($this->business->officeHour->start_time)->format('h:i A');
        $business_end_time = Carbon::parse($this->business->officeHour->end_time)->format('h:i A');
        $check_weekend = new CheckWeekend();
        $weekend_settings = $this->businessWeekendRepo->getAllByBusiness($this->business);
        $business_holiday = $this->business_holiday_repo->getAllByBusiness($this->business);
        foreach ($business_holiday as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $data[] = $d->format('Y-m-d');
            }
        }
        $dates_of_holidays_formatted = $data;
        foreach($this->shift_calender as $employeeShift)
        {
            $temp = $temp_calender = [];
            $temp_calender =  [
                'id' => $employeeShift->id,
                'date' => $employeeShift->date,
                'is_general' => $employeeShift->is_general,
                'is_unassigned' => $employeeShift->is_unassigned,
                'is_shift' => $employeeShift->is_shift,
                'color_code' => $employeeShift->color_code
            ];

            if($employeeShift->is_general)
            {
                $date = Carbon::parse($employeeShift->date);
                $weekend_day = $check_weekend->getWeekendDays($date, $weekend_settings);
                if($this->isWeekend($date, $weekend_day)) $temp_calender['shift_name'] = 'Weekend';
                elseif ($this->isHoliday($date, $dates_of_holidays_formatted)) $temp_calender['shift_name'] = 'Holiday';
                else
                {
                    $name = 'General';
                    $temp_calender['shift_name'] = $name;
                    $temp_calender['shift_start'] = $business_start_time;
                    $temp_calender['shift_end'] = $business_end_time;
                    $temp = $this->makeShiftData($employeeShift->id, $name, $employeeShift->color_code, $business_start_time, $business_end_time);
                }
            }
            elseif($employeeShift->is_shift)
            {
                $temp_calender['shift_name'] = $employeeShift->shift->title;
                $temp_calender['shift_start'] = Carbon::parse($employeeShift->start_time)->format('h:i A');
                $temp_calender['shift_end'] = Carbon::parse($employeeShift->end_time)->format('h:i A');
                $temp = $this->makeShiftData($employeeShift->id, $employeeShift->shift->title, $employeeShift->color_code, $employeeShift->shift->start_time, $employeeShift->shift->end_time);
            }
            elseif($employeeShift->is_unassigned) $temp_calender['shift_name'] = 'Unassigned';

            $shifts[] = $temp;
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
            'id'    => $id,
            'title'         => $name,
            'color_code'    => $color_code,
            'start_time'    => $start_time,
            'end_time'      => $end_time
        ];
    }

    /**
     * @param Carbon $date
     * @param $weekend_day
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }

    /**
     * @param Carbon $date
     * @param $holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }
}