<?php namespace App\Sheba\AccountingEntry\Service;

use App\Sheba\AccountingEntry\Constants\UserType;
use App\Sheba\AccountingEntry\Helper\AccountingHelper;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use Illuminate\Http\Request;
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
    public function setStartDate($startDate)
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
    public function getBalance(Request $request)
    {
        $startDate = AccountingHelper::convertStartDate($this->startDate);
        $endDate = AccountingHelper::convertEndDate($this->endDate);
        $contact_type = $request->contact_type;
        if ($endDate < $startDate) {
            return http_response($request, null, 400, ['message' => 'End date can not be smaller than start date']);
        }
        return $this->dueTrackerRepo->getBalance($this->partner->id, $startDate, $endDate, $contact_type);
    }

}