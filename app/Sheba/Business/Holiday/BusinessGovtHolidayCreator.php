<?php namespace App\Sheba\Business\Holiday;

use App\Sheba\Business\BusinessCommonInformation;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\GovernmentHolidays\Contract as GovernmentHolidayRepoInterface;
use Sheba\ModificationFields;
use Sheba\Business\Holiday\CreateRequest;

class BusinessGovtHolidayCreator
{
    use ModificationFields;
    /** @var BusinessHolidayRepoInterface $holidayRepository */
    private $holidayRepository;
    /** @var GovernmentHolidayRepoInterface $governmentHolidayRepository */
    private $governmentHolidayRepository;
    /** @var CreateRequest $businessGovtHolidayCreatorRequest */
    private $businessGovtHolidayCreatorRequest;

    public function __construct(BusinessHolidayRepoInterface $holiday_repo, GovernmentHolidayRepoInterface $government_holidayRepo)
    {
        $this->holidayRepository = $holiday_repo;
        $this->governmentHolidayRepository = $government_holidayRepo;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setBusinessGovtHolidayCreatorRequest(CreateRequest $create_request)
    {
        $this->businessGovtHolidayCreatorRequest = $create_request;
        return $this;
    }

    public function create()
    {
        $govt_holidays = $this->governmentHolidayRepository->builder()->select('id', 'title', 'start_date', 'end_date')->get();
        foreach ($govt_holidays as $govt_holiday) {
            $this->holidayRepository->create($this->withCreateModificationField([
                'business_id' => $this->businessGovtHolidayCreatorRequest->getBusiness()->id,
                'title' => $govt_holiday->title,
                'start_date' => $govt_holiday->start_date,
                'end_date' => $govt_holiday->end_date,
            ]));
        }
    }
}