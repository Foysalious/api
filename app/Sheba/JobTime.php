<?php

namespace App\Sheba;


use Carbon\Carbon;

class JobTime
{
    private $schedule_date;
    private $preferred_time;
    public $isValid;
    public $error_message = '';

    public function __construct($schedule_date, $preferred_time)
    {
        $this->schedule_date = $schedule_date != null ? $schedule_date : Carbon::now()->toDateString();
        $this->preferred_time = $preferred_time != null ? $preferred_time : 'Anytime';
    }

    public function calculateIsValid()
    {
        if (!$this->isValidDate()) {
            $this->error_message .= "Schedule Date is Invalid";
            $this->isValid = 0;
        } elseif (!$this->isValidTime()) {
            $this->isValid = 0;
            $this->error_message .= "Preferred Time is Invalid";
        }
        $this->isValid = 1;
    }

    private function isValidDate()
    {
        return Carbon::parse($this->schedule_date) >= Carbon::now()->toDateString();
    }

    private function isValidTime()
    {
        return array_has($this->getSelectableTimes(), $this->preferred_time);
    }

    private function getSelectableTimes()
    {
        $today_slots = [];
        foreach (constants('JOB_PREFERRED_TIMES') as $time) {
            if ($time == "Anytime" || Carbon::now()->lte(Carbon::createFromTimestamp(strtotime(explode(' - ', $time)[1])))) {
                $today_slots[$time] = $time;
            }
        }
        return $today_slots;
    }
}