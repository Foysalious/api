<?php namespace App\Sheba\Business\Holiday;

use App\Sheba\Business\BusinessCommonInformation;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\GovernmentHolidays\Contract as GovernmentHolidayRepoInterface;
use Sheba\ModificationFields;

class InitialHolidayBusinessCommonInformationCreator extends BusinessCommonInformation
{
    use ModificationFields;
    /** @var BusinessHolidayRepoInterface $holidayRepository */
    private $holidayRepository;
    /** @var GovernmentHolidayRepoInterface $governmentHolidayRepository */
    private $governmentHolidayRepository;

    public function __construct(BusinessHolidayRepoInterface $holiday_repo, GovernmentHolidayRepoInterface $government_holidayRepo)
    {
        $this->holidayRepository = $holiday_repo;
        $this->governmentHolidayRepository = $government_holidayRepo;
    }

    public function create()
    {
        $govt_holidays = $this->governmentHolidayRepository->builder()->select('id', 'title', 'start_date', 'end_date')->get();
        foreach ($govt_holidays as $govt_holiday) {
            $this->holidayRepository->create($this->withCreateModificationField([
                'business_id' => $this->business->id,
                'title' => $govt_holiday->title,
                'start_date' => $govt_holiday->start_date,
                'end_date' => $govt_holiday->end_date,
            ]));
        }
    }
}