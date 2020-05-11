<?php namespace Sheba\Business\Holiday;

use Sheba\ModificationFields;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessHoliday\Model as Holiday;

class Updater
{
    use ModificationFields;

    /** @var BusinessHolidayRepoInterface $holidayRepository */
    private $holidayRepository;
    /** @var CreateRequest $businessHolidayCreatorRequest */
    private $businessHolidayCreatorRequest;
    /** @var Holiday $holiday */
    private $holiday;

    public function __construct(BusinessHolidayRepoInterface $business_holidays_repo)
    {
        $this->holidayRepository = $business_holidays_repo;
        return $this;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setBusinessHolidayCreatorRequest(CreateRequest $create_request)
    {
        $this->businessHolidayCreatorRequest = $create_request;
        return $this;
    }

    /**
     * @param Holiday $holiday
     * @return $this
     */
    public function setHoliday(Holiday $holiday)
    {
        $this->holiday = $holiday;
        return $this;
    }

    public function update()
    {
        $this->setModifier($this->businessHolidayCreatorRequest->getMember());
        return $this->holiday->update($this->withUpdateModificationField([
            'title' => $this->businessHolidayCreatorRequest->getHolidayName(),
            'start_date' => $this->businessHolidayCreatorRequest->getStartDate(),
            'end_date' => $this->businessHolidayCreatorRequest->getEndDate(),
        ]));
    }
}