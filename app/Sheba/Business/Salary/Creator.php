<?php namespace App\Sheba\Business\Salary;


use App\Models\BusinessMember;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Salary\SalaryRepository;

class Creator
{
    /** @var Requester */
    private $salaryRequest;
    private $salaryData = [];
    private $businessMember;
    /** @var SalaryRepository */
    private $salaryRepositry;

    /**
     * Updater constructor.
     * @param SalaryRepository $salary_repositry
     */
    public function __construct(SalaryRepository $salary_repositry)
    {
        $this->salaryRepositry = $salary_repositry;
    }

    /** @param $salary_request */
    public function setSalaryRequester(Requester $salary_request)
    {
        $this->salaryRequest = $salary_request;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        $salary = null;
        DB::transaction(function () use ($salary) {
            $salary = $this->salaryRepositry->create($this->salaryData);
        });
        return $salary;
    }

    private function makeData()
    {
        $this->salaryData['business_member_id'] = $this->businessMember->id;
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

}
