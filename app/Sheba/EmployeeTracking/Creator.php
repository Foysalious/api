<?php namespace App\Sheba\EmployeeTracking;


use Illuminate\Support\Facades\DB;

class Creator
{
    /** @var Requester  $requester **/
    private $requester;
    private $visitData = [];

    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        DB::transaction(function () {

        });
    }

    private function makeData()
    {
        $this->visitData = [
            'business_member_id' => $this->requester->getBusinessMember(),
            'date' => $this->requester->getDate(),
            'employee' => $this->requester->getEmployee(),
            'title' => $this->requester->getTitle(),
            'description' => $this->requester->getDescription(),
        ];
    }

}