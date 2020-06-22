<?php namespace App\Sheba\Business\AttendanceType;

use App\Sheba\Business\BusinessCommonInformation;
use Sheba\Dal\BusinessAttendanceTypes\Contract as BusinessAttendanceTypesRepoInterface;
use Sheba\ModificationFields;
use Sheba\Business\AttendanceType\CreateRequest;

class Creator
{
    use ModificationFields;
    /** @var BusinessAttendanceTypesRepoInterface $attendanceTypesRepository */
    private $attendanceTypesRepository;
    /** @var CreateRequest $attendanceTypesCreateRequest */
    private $attendanceTypesCreateRequest;

    public function __construct(BusinessAttendanceTypesRepoInterface $attendance_types_repo)
    {
        $this->attendanceTypesRepository = $attendance_types_repo;
    }


    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setAttendanceTypeCreateRequest(CreateRequest $create_request)
    {
        $this->attendanceTypesCreateRequest = $create_request;
        return $this;
    }

    public function create()
    {
        $this->attendanceTypesRepository->create($this->withCreateModificationField([
            'business_id' => $this->attendanceTypesCreateRequest->getBusiness()->id,
            'attendance_type' => $this->attendanceTypesCreateRequest->getAttendanceType(),
        ]));
    }
}