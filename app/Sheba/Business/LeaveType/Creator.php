<?php namespace Sheba\Business\LeaveType;

use App\Models\Business;
use App\Models\Member;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    /** @var LeaveTypesRepoInterface $leaveTypeRepository */
    private $leaveTypeRepository;
    /** @var Business $business */
    private $business;
    /** @var Member $member */
    private $member;
    private $title;
    private $total_days;

    public function __construct(LeaveTypesRepoInterface $leave_type_repo)
    {
        $this->leaveTypeRepository = $leave_type_repo;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param Member $member
     * @return $this
     */
    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param $total_days
     * @return $this
     */
    public function setTotalDays($total_days)
    {
        $this->total_days = $total_days;
        return $this;
    }

    public function create()
    {
        $this->setModifier($this->member);
        $data = [
            'business_id' => $this->business->id,
            'title' => $this->title,
            'total_days' => $this->total_days
        ];

        return $this->leaveTypeRepository->create($this->withCreateModificationField($data));
    }
}
