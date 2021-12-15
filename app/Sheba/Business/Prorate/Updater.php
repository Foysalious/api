<?php namespace Sheba\Business\Prorate;

use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Dal\BusinessMemberLeaveType\Model as BusinessMemberLeaveType;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    /** @var BusinessMemberLeaveTypeInterface $businessMemberLeaveTypeRepo */
    private $businessMemberLeaveTypeRepo;
    /** @var Requester $requester */
    private $requester;
    /**@var BusinessMemberLeaveType $businessMemberLeaveType */
    private $businessMemberLeaveType;
    private $data;

    /**
     * Creator constructor.
     * @param BusinessMemberLeaveTypeInterface $business_member_leave_type_repo
     */
    public function __construct(BusinessMemberLeaveTypeInterface $business_member_leave_type_repo)
    {
        $this->businessMemberLeaveTypeRepo = $business_member_leave_type_repo;
    }

    /**
     * @param Requester $requester
     * @return $this
     */
    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }

    /**
     * @param BusinessMemberLeaveType $business_member_leave_type
     * @return $this
     */
    public function setBusinessMemberLeaveType(BusinessMemberLeaveType $business_member_leave_type)
    {
        $this->businessMemberLeaveType = $business_member_leave_type;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        $this->businessMemberLeaveTypeRepo->update($this->businessMemberLeaveType, $this->data);
    }

    private function makeData()
    {
        if ($this->requester->getLeaveTypeId()) $this->data['leave_type_id'] = $this->requester->getLeaveTypeId();
        if ($this->requester->getTotalDays() !== null) $this->data['total_days'] = $this->requester->getTotalDays();
        if ($this->requester->getNote()) $this->data['note'] = $this->requester->getNote();
    }
}