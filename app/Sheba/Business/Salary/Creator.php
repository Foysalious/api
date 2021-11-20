<?php namespace Sheba\Business\Salary;

use App\Sheba\Business\Salary\Component\Maker;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Salary\SalaryRepository;

class Creator
{
    /** @var Requester */
    private $salaryRequest;
    private $salaryData = [];
    /** @var SalaryRepository */
    private $salaryRepository;
    private $salary;

    /**
     * Updater constructor.
     * @param SalaryRepository $salary_repository
     */
    public function __construct(SalaryRepository $salary_repository)
    {
        $this->salaryRepository = $salary_repository;
    }

    /**
     * @param Requester $salary_request
     * @return Creator
     */
    public function setSalaryRequester(Requester $salary_request)
    {
        $this->salaryRequest = $salary_request;
        return $this;
    }

    public function create()
    {
        $this->makeData();
        DB::transaction(function () {
            $this->salary = $this->salaryRepository->create($this->salaryData);
            if (!$this->salaryRequest->getIsForBulkGrossSalary()) $this->createComponentPercentage();
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
        foreach ($breakdown_percentage as $component) {
            $gross_salary_breakdown_maker = new Maker($component, $business_member, $this->salary, null);
            $gross_salary_breakdown_maker->setManager($this->salaryRequest->getManagerMember());
            $gross_salary_breakdown_maker->runComponent();
        }
    }

}
