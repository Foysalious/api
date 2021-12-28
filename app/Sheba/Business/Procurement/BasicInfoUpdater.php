<?php namespace Sheba\Business\Procurement;

use Carbon\Carbon;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProcurementRepositoryInterface;

class BasicInfoUpdater
{
    use ModificationFields;

    private $procurementRepo;
    private $title;
    private $budget;
    private $lastDateOfSubmission;
    private $procurement;

    /**
     * BasicInfoUpdater constructor.
     * @param ProcurementRepositoryInterface $procurement_repository
     */
    public function __construct(ProcurementRepositoryInterface $procurement_repository)
    {
        $this->procurementRepo = $procurement_repository;
    }

    public function setProcurement($procurement)
    {
        $this->procurement = $procurement;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setBudget($budget)
    {
        $this->budget = $budget;
        return $this;
    }

    public function setLastDateOfSubmission($last_date_of_submission)
    {
        $this->lastDateOfSubmission = Carbon::parse($last_date_of_submission)->endOfDay();
        return $this;
    }

    public function updateForDraft()
    {
        $data = [
            'title' => $this->title,
            'estimated_price' => $this->budget,
            'last_date_of_submission' => $this->lastDateOfSubmission
        ];
        $this->update($data);
    }

    public function updateForOpen()
    {
        $data = [
            'last_date_of_submission' => $this->lastDateOfSubmission
        ];
        $this->update($data);
    }

    public function update($data)
    {
        $this->procurementRepo->update($this->procurement, $this->withUpdateModificationField($data));
    }
}
