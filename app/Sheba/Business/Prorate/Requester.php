<?php namespace Sheba\Business\Prorate;

class Requester
{
    private $business;
    private $note;
    private $totalDays;
    private $leaveTypeId;
    private $businessMemberIds;

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param $business
     * @return $this
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param $total_days
     * @return $this
     */
    public function setTotalDays($total_days)
    {
        $this->totalDays = $total_days;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalDays()
    {
        return $this->totalDays;
    }

    /**
     * @param $leave_type_id
     * @return $this
     */
    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveTypeId = $leave_type_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLeaveTypeId()
    {
        return $this->leaveTypeId;
    }

    /**
     * @param $business_member_ids
     * @return $this
     */
    public function setBusinessMemberIds(array $business_member_ids)
    {
        $this->businessMemberIds = $business_member_ids;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessMemberIds()
    {
        return $this->businessMemberIds;
    }

}