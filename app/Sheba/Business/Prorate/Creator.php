<?php namespace Sheba\Business\Prorate;

use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    /** @var BusinessMemberLeaveTypeInterface $businessMemberLeaveTypeRepo */
    private $businessMemberLeaveTypeRepo;
    /** @var Requester $requester */
    private $requester;
    private $data;
    private $oldBusinessMemberLeaveTypes;

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

    public function getOldBusinessMemberLeaveType()
    {
        $this->oldBusinessMemberLeaveTypes = $this->businessMemberLeaveTypeRepo->builder()
            ->whereIn('business_member_id', $this->requester->getBusinessMemberIds())
            ->where('leave_type_id', $this->requester->getLeaveTypeId());
    }

    public function create()
    {
        $this->getOldBusinessMemberLeaveType();

        foreach ($this->requester->getBusinessMemberIds() as $business_member_id) {
            if ($old_business_member_type = $this->oldBusinessMemberLeaveTypes->where('business_member_id', $business_member_id)->first()) $this->businessMemberLeaveTypeRepo->delete($old_business_member_type);
            $this->data[] = [
                'business_member_id' => $business_member_id,
                'leave_type_id' => $this->requester->getLeaveTypeId(),
                'total_days' => $this->requester->getTotalDays(),
                'note' => $this->requester->getNote()
            ];
        }

        $this->businessMemberLeaveTypeRepo->insert($this->data);
    }
}