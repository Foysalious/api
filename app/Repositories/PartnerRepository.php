<?php


namespace App\Repositories;


use App\Models\Partner;

class PartnerRepository
{
    private $_partner;
    private $_date = null;
    private $_time = '';

    public function __construct($_partner, $request = null)
    {
        if (array_key_exists('day', $request)) {
            $this->_date = $request['day'];
        }
        if (array_key_exists('time', $request)) {
            $this->_time = $request['time'];
        }
        $this->_partner = ($_partner) instanceof Partner ? $_partner : Partner::find($_partner);
    }

    public function available()
    {
        if ($this->_partnerOnLeave()) {
            return false;
        }
        if (!$this->_worksAtThisDay()) {
            return false;
        }
        if (!$this->_worksAtThisTime()) {
            return false;
        }
        return true;
    }

    private function _worksAtThisDay()
    {
        if ($this->_date != null) {
            $day = date('l', strtotime($this->_date)); // extract day from date
        } else {
            $day = date('l'); // set to today's day
        }
        return in_array($day, json_decode($this->_partner->basicInformations->working_days));
    }

    private function _partnerOnLeave()
    {
        return $this->_partner->runningLeave($this->_date) != null ? true : false;
    }

    private function _worksAtThisTime()
    {
        if ($this->_time != '') {
            if (array_has(constants('JOB_PREFERRED_TIMES'), $this->_time) && $this->_time != 'Anytime') {
                $working_hours = json_decode($this->_partner->basicInformations->working_hours);
                return $this->_betweenWorkingHours($working_hours, constants('JOB_START_END_TIMES')[$this->_time]);
            }
        }
        return true;
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