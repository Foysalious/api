<?php namespace App\Sheba\Business\Salary;

use App\Models\BusinessMember;
use App\Sheba\Business\Salary\Component\Maker;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\Salary\SalaryRepository;

class Creator
{
    /** @var Requester */
    private $salaryRequest;
    private $salaryData = [];
    private $businessMember;
    /** @var SalaryRepository */
    private $salaryRepository;
    /*** @var PayrollComponentRepository*/
    private $payrollComponentRepository;
    private $salary;

    /**
     * Updater constructor.
     * @param SalaryRepository $salary_repository
     */
    public function __construct(SalaryRepository $salary_repository)
    {
        $this->salaryRepository = $salary_repository;
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
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
        DB::transaction(function () {
            $this->salary = $this->salaryRepository->create($this->salaryData);
            $this->createComponentPercentage();
        });

        return true;
    }

    private function makeData()
    {
        $this->salaryData['business_member_id'] = $this->salaryRequest->getBusinessMember()->id;
        $this->salaryData['gross_salary'] = $this->salaryRequest->getGrossSalary();
    }

    private function createComponentPercentage()
    {
        $business_member = $this->salaryRequest->getBusinessMember();
        $breakdown_percentage = $this->salaryRequest->getBreakdownPercentage();
        if (empty($breakdown_percentage)) return true;
        foreach ( $breakdown_percentage as $component) {
            $gross_salary_breakdown_maker = new Maker($component, $business_member, $this->salary, null);
            $gross_salary_breakdown_maker->setManager($this->salaryRequest->getManagerMember());
            $gross_salary_breakdown_maker->runComponent();
        }
    }

}
