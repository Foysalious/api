<?php


namespace App\Repositories;


use App\Models\Partner;

class PartnerRepository
{
    private $_partner;

    public function __construct($_partner)
    {
        $this->_partner = ($_partner) instanceof Partner ? $_partner : Partner::find($_partner);
    }

    public function available($data)
    {
        $date = array_key_exists('day', $data) ? $data['day'] : date('Y-m-d');
        $time = array_key_exists('time', $data) ? $data['time'] : 'Anytime';
        if ($this->_partnerOnLeave($date)) {
            return false;
        }
        if (!$this->_worksAtThisDay($date)) {
            return false;
        }
        if (!$this->_worksAtThisTime($time)) {
            return false;
        }
        return true;
    }

    private function _worksAtThisDay($date)
    {
        $day = date('l', strtotime($date));
        return in_array($day, json_decode($this->_partner->basicInformations->working_days));
    }

    private function _partnerOnLeave($date)
    {
        $date = $date . ' ' . date('H:i:s');
        return $this->_partner->runningLeave($date) != null ? true : false;
    }

    /**
     * @param $time
     * @return bool
     */
    private function _worksAtThisTime($time)
    {
        //Means customer is available at anytime, no need to check partner working hours
        if ($time == 'Anytime') {
            return true;
        }
        if (array_has(constants('JOB_PREFERRED_TIMES'), $time)) {
            $working_hours = json_decode($this->_partner->basicInformations->working_hours);
            return $this->_betweenWorkingHours($working_hours, constants('JOB_START_END_TIMES')[$time]);
        }
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