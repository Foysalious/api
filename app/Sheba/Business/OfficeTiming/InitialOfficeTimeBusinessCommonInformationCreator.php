<?php namespace App\Sheba\Business\OfficeTiming;

use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use App\Sheba\Business\BusinessCommonInformation;
use Sheba\ModificationFields;

class InitialOfficeTimeBusinessCommonInformationCreator extends BusinessCommonInformation
{
    use ModificationFields;
    /** @var BusinessOfficeHoursRepoInterface $officeHoursRepository */
    private $officeHoursRepository;

    public function __construct(BusinessOfficeHoursRepoInterface $office_hours_repo)
    {
        $this->officeHoursRepository = $office_hours_repo;
    }

    public function create()
    {
        $this->officeHoursRepository->create($this->withCreateModificationField([
            'business_id' => $this->business->id,
            'start_time' => '09:00:59',
            'end_time' => '17:00:00',
        ]));
    }
}