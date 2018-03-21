<?php

namespace App\Sheba\Partner;


use App\Models\Partner;
use Carbon\Carbon;

class PartnerAvailable
{
    private $partner;

    public function __construct($partner)
    {
        $this->partner = ($partner) instanceof Partner ? $partner : Partner::find($partner);
    }

    public function available($date, $preferred_time, $category_id)
    {
        if ($this->_partnerOnLeave($date)) {
            return 0;
        }
        /*if (!$this->_worksAtThisDay($date)) {
            return 0;
        }
        if (!$this->_worksAtThisTime($preferred_time)) {
            return 0;
        }*/
        if (!$this->_worksAtDayAndTime($date, $preferred_time)) {
            return 0;
        }
        if (!scheduler($this->partner)->isAvailable($date, explode('-', $preferred_time)[0], $category_id)) {
            return 0;
        }
        return 1;
    }

    private function _partnerOnLeave($date)
    {
        $date = $date . ' ' . date('H:i:s');
        return $this->partner->runningLeave($date) != null ? true : false;
    }

    private function _worksAtThisDay($date)
    {
        $day = date('l', strtotime($date));
        //working days of partner is empty or has empty array
        if (preg_match("(\[]|^$)", $this->partner->basicInformations->working_days) === 1) {
            return false;
        }
        return in_array($day, json_decode($this->partner->basicInformations->working_days));
    }

    private function _worksAtThisTime($preferred_time)
    {
        $working_hours = json_decode($this->partner->basicInformations->working_hours);
        $start_time = Carbon::parse(explode('-', $preferred_time)[0]);
        return $start_time->gte(Carbon::parse($working_hours->day_start)) && $start_time->lte(Carbon::parse($working_hours->day_end));
    }

    private function _worksAtDayAndTime($date, $time)
    {
        $day = Carbon::parse($date)->format('l');
        $working_day = $this->partner->workingHours->where('day', $day)->first();
        if (!$working_day) return false;
        $start_time = Carbon::parse(explode('-', $time)[0]);
        return $start_time->gte(Carbon::parse($working_day->start_time))
            && $start_time->lte(Carbon::parse($working_day->end_time));
    }

    private function _betweenWorkingHours($working_hours, $times)
    {
        $fail = 0;
        foreach ($times as $time) {
            $time = strtotime($time);
            // time doesn't fall between working hour
            if (!(strtotime($working_hours->day_start) <= $time && $time <= strtotime($working_hours->day_end))) {
                $fail++;
            }
        }
        // If both start & end time don't fall between working hour return false
        return $fail == 2 ? false : true;
    }
}