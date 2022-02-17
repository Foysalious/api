<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;

class DueTrackerService
{
    protected $partner;
    protected $dueTrackerRepo;
    protected $contactType;

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
     * @throws AccountingEntryServerError
     */
    public function getBalance()
    {
        return $this->dueTrackerRepo->getBalance($this->partner->id, $this->contactType);
    }

}