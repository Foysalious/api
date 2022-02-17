<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class DueTrackerService
{
    protected $partner;
    protected $dueTrackerRepo;
    protected $contactType;
    protected $startDate;
    protected $endDate;

    public function __construct(DueTrackerRepositoryV2  $dueTrackerRepo)
    {
        $this->dueTrackerRepo = $dueTrackerRepo;
    }

    /**
     * @param mixed $partner
     */
    public function setPartner($partner): DueTrackerService
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $contactType
     */
    public function setContactType($contactType): DueTrackerService
    {
        $this->contactType = $contactType;
        return $this;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): DueTrackerService
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate): DueTrackerService
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @throws AccountingEntryServerError
     */
    public function getBalance()
    {
        return $this->dueTrackerRepo->getBalance($this->partner->id, $this->startDate, $this->endDate, $this->contactType);
    }

}