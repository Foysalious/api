<?php namespace Sheba\Business\Weekend;


class CreateRequest
{
    private $business;
    private $weekday;

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
    public function getWeekday()
    {
        return $this->weekday;
    }

    /**
     * @param $weekday
     * @return CreateRequest
     */
    public function setWeekday($weekday)
    {
        $this->weekday = $weekday;
        return $this;
    }
}