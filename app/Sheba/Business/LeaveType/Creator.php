<?php namespace App\Sheba\Business\LeaveType;

use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use Sheba\ModificationFields;

class Creator {
    use ModificationFields;
    private $leaveTypeRepository;
    const ANNUAL = 'Annual Leave';
    const SICK = 'Sick Leave';
    const ANNUAL_DAYS = 21;
    const SICK_DAYS = 14;

    public function __construct(LeaveTypesRepoInterface $leave_type_repo)
    {
        $this->leaveTypeRepository = $leave_type_repo;
    }


    /**
     * @param $member
     * @param $business_id
     * @return array
     */
    public function createDefaultLeaveType($member, $business_id)
    {
        $this->setModifier($member);
        $annual_leave_data = [
            'business_id' => $business_id,
            'title' => self::ANNUAL,
            'total_days' => self::ANNUAL_DAYS
        ];
        $annual_leave = $this->leaveTypeRepository->create($this->withCreateModificationField($annual_leave_data));
        $sick_leave_data = [
            'business_id' => $business_id,
            'title' => self::SICK,
            'total_days' => self::SICK_DAYS
        ];
        $sick_leave = $this->leaveTypeRepository->create($this->withCreateModificationField($sick_leave_data));
        $data = [
            'annual_leave' => $annual_leave,
            'sick_leave' => $sick_leave
        ];

        return $data;
    }
}