<?php namespace App\Sheba\EmployeeTracking;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\Visit\VisitRepository;

class Updater
{
    /** @var VisitRepository $visitRepository*/
    private $visitRepository;

    public function __construct()
    {
        $this->visitRepository = app(VisitRepository::class);
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
        $visitor = $employee_id ? $employee_id : $business_member_id;
        $this->visitData = [
            'visitor_id' => $visitor,
            'schedule_date' => $this->requester->getDate(),
            'title' => $this->requester->getTitle(),
            'description' => $this->requester->getDescription(),
        ];
        if ($employee_id) $this->visitData['assignee_id'] = $business_member_id;
    }

}