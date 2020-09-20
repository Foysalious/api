<?php namespace Sheba\Business\Holiday;


class CreateRequest
{
    private $business;
    private $member;
    private $startDate;
    private $endDate;
    private $holidayName;

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
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param mixed $member
     * @return CreateRequest
     */
    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHolidayName()
    {
        return $this->holidayName;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setHolidayName($title)
    {
        $this->holidayName = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param $start_date
     * @return $this
     */
    public function setStartDate($start_date)
    {
        $this->startDate = $start_date . ' ' . '00:00:00';
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param $end_date
     * @return $this
     */
    public function setEndDate($end_date)
    {
        $this->endDate = $end_date . ' ' . '23:59:59';
        return $this;
    }

}