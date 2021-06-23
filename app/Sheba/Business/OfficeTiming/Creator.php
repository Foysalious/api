<?php namespace App\Sheba\Business\OfficeTiming;

use Sheba\Business\OfficeTiming\CreateRequest;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use App\Sheba\Business\BusinessCommonInformation;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    /** @var BusinessOfficeHoursRepoInterface $officeHoursRepository */
    private $officeHoursRepository;
    /** @var CreateRequest $officeTimingCreateRequest */
    private $officeTimingCreateRequest;

    /**
     * Creator constructor.
     * @param BusinessOfficeHoursRepoInterface $office_hours_repo
     */
    public function __construct(BusinessOfficeHoursRepoInterface $office_hours_repo)
    {
        $this->officeHoursRepository = $office_hours_repo;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setOfficeTimingCreateRequest(CreateRequest $create_request)
    {
        $this->officeTimingCreateRequest = $create_request;
        return $this;
    }

    public function create()
    {
        $this->officeHoursRepository->create($this->withCreateModificationField([
            'business_id' => $this->officeTimingCreateRequest->getBusiness()->id,
            'start_time' => $this->officeTimingCreateRequest->getStartTime(),
            'end_time' => $this->officeTimingCreateRequest->getEndTime(),
            'type' => $this->officeTimingCreateRequest->getTotalWorkingDaysType(),
            'is_for_late_checkin' => $this->officeTimingCreateRequest->getIsForLateCheckinPolicy(),
            'is_for_early_checkout' => $this->officeTimingCreateRequest->getIsForEarlyCheckoutPolicy()
        ]));
    }
}
