<?php namespace Sheba\Resource\Schedule;


class ResourceScheduleChecker
{
    protected $schedules;
    protected $date;
    protected $time;

    /**
     * @param $schedules
     * @return ResourceScheduleChecker
     */
    public function setSchedules($schedules)
    {
        $this->schedules = $schedules;
        return $this;
    }

    /**
     * @param $date
     * @return ResourceScheduleChecker
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param $time
     * @return ResourceScheduleChecker
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return array
     */
    public function checkScheduleAvailability()
    {
        $schedule = [];
        foreach ($this->schedules as $date) {
            if ($date['value'] == $this->date) {
                $schedule['date'] = $date['value'];
                foreach ($date['slots'] as $slot) {
                    if ($slot['key'] == $this->time) {
                        $schedule['slot'] = $slot;
                        break;
                    }
                }
            }
        }
        $schedule = isset($schedule['date']) && isset($schedule['slot']) ? $schedule : [];
        return $schedule;
    }
}