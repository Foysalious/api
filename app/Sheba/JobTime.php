<?php

namespace App\Sheba;


use Carbon\Carbon;

class JobTime
{
    private $schedule_date;
    private $preferred_time;
    private $today;
    public $isValid = 1;
    public $error_message = '';

    public function __construct($schedule_date, $preferred_time)
    {
        $this->schedule_date = $schedule_date != null ? Carbon::parse($schedule_date) : Carbon::now()->toDateString();
        $this->preferred_time = $preferred_time != null ? $preferred_time : 'Anytime';
        $this->today = Carbon::now()->toDateString();
    }

    public function validate()
    {
        if (!$this->isValidDate()) {
            $this->error_message .= "Schedule Date is Invalid";
            $this->isValid = 0;
        } elseif (!$this->isValidTime()) {
            $this->isValid = 0;
            $this->error_message .= "Preferred Time is Invalid";
        }
        return $this->isValid;
    }

    private function isValidDate()
    {
        return $this->schedule_date >= $this->today;
    }

    private function isValidTime()
    {
        if ($this->schedule_date > $this->today) {
            return 1;
        }
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