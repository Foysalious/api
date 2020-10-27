<?php namespace Sheba\Business\Prorate;

class Requester
{
    private $business;
    private $note;
    private $totalDays;
    private $leaveTypeId;
    private $businessMemberIds;

    public function getBusiness()
    {
        return $this->business;
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }


    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }


    public function setTotalDays($total_days)
    {
        $this->totalDays = $total_days;
        return $this;
    }

    public function getTotalDays()
    {
        return $this->totalDays;
    }

    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveTypeId = $leave_type_id;
        return $this;
    }

    public function getLeaveTypeId()
    {
        return $this->leaveTypeId;
    }

    public function setBusinessMemberIds($business_member_ids)
    {
        $this->businessMemberIds = $business_member_ids;
        return $this;
    }

    public function getBusinessMemberIds()
    {
        return $this->businessMemberIds;
    }

}