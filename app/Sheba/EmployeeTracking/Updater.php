<?php namespace App\Sheba\EmployeeTracking;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\Visit\VisitRepoImplementation;

class Updater
{
    /** @var VisitRepoImplementation $visitRepository*/
    private $visitRepository;

    public function __construct()
    {
        $this->visitRepository = app(VisitRepoImplementation::class);
    }
    /** @var Requester  $requester **/
    private $requester;
    private $visitData = [];

    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }

    public function update()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->visitRepository->update($this->requester->getEmployeeVisit(), $this->visitData);
        });
    }

    private function makeData()
    {
        $business_member_id = $this->requester->getBusinessMember()->id;
        $employee_id = $this->requester->getEmployee();
        $this->visitData = [
            'visitor_id' => $employee_id,
            'schedule_date' => $this->requester->getDate(),
            'title' => $this->requester->getTitle(),
            'description' => $this->requester->getDescription(),
        ];
        if ($business_member_id !== $employee_id) $this->visitData['assignee_id'] = $business_member_id;
    }

}