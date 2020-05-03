<?php namespace App\Sheba\Business\AttendanceType;

use App\Sheba\Business\BusinessCommonInformation;
use Sheba\Dal\BusinessAttendanceTypes\Contract as BusinessAttendanceTypesRepoInterface;
use Sheba\ModificationFields;

class InitialAttendanceTypeBusinessCommonInformationCreator extends BusinessCommonInformation
{
    use ModificationFields;
    /** @var BusinessAttendanceTypesRepoInterface $attendanceTypesRepository */
    private $attendanceTypesRepository;

    public function __construct(BusinessAttendanceTypesRepoInterface $attendance_types_repo)
    {
        $this->attendanceTypesRepository = $attendance_types_repo;
    }

    public function create()
    {
        $this->attendanceTypesRepository->create($this->withCreateModificationField([
            'business_id' => $this->business->id,
            'attendance_type' => 'remote',
        ]));
    }
}