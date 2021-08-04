<?php namespace Sheba\Business\OfficeTiming;


class CreateRequest
{
    private $business;
    private $startTime;
    private $endTime;
    private $workingDaysType;
    private $isForLateCheckinEnable;
    private $isForEarlyCheckoutEnable;

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param mixed $business
     * @return CreateRequest
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param $start_time
     * @return CreateRequest
     */
    public function setStartTime($start_time)
    {
        $this->startTime = $start_time;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param $end_time
     * @return CreateRequest
     */
    public function setEndTime($end_time)
    {
        $this->endTime = $end_time;
        return $this;
    }

    public function setTotalWorkingDaysType($working_days_type)
    {
        $this->workingDaysType = $working_days_type;
        return $this;
    }

    public function getTotalWorkingDaysType()
    {
        return $this->workingDaysType;
    }

    public function setIsForLateCheckinPolicy($is_enable)
    {
        $this->isForLateCheckinEnable = $is_enable;
        return $this;
    }

    public function setIsForEarlyCheckoutPolicy($is_enable)
    {
        $this->isForEarlyCheckoutEnable = $is_enable;
        return $this;
    }

    public function getIsForLateCheckinPolicy()
    {
        return $this->isForLateCheckinEnable;
    }

    public function getIsForEarlyCheckoutPolicy()
    {
        return $this->isForEarlyCheckoutEnable;
    }
}
