<?php namespace Sheba\Business\Payslip\PayRun;

use App\Sheba\Business\Salary\Requester as SalaryRequester;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Business\Payslip\Updater as PayslipUpdater;

class Updater
{
    private $payrunData;
    private $salaryRequester;
    private $salaryRepository;
    private $managerMember;
    private $payslipUpdater;


    /**
     * Updater constructor.
     * @param SalaryRequester $salary_requester
     * @param SalaryRepository $salary_repository
     * @param PayslipUpdater $payslip_updater
     */
    public function __construct(SalaryRequester $salary_requester, SalaryRepository $salary_repository, PayslipUpdater $payslip_updater)
    {
        $this->salaryRequester = $salary_requester;
        $this->salaryRepository = $salary_repository;
        $this->payslipUpdater = $payslip_updater;
    }

    public function setData($data)
    {
        $this->payrunData = json_decode($data, 1);
        return $this;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function update()
    {
        DB::transaction(function () {
            foreach ($this->payrunData as $data) {
                $this->salaryRequester->setBusinessMember($data['id'])->setGrossSalary($data['amount'])->setManagerMember($this->managerMember)->createOrUpdate();
                $this->payslipUpdater->setBusinessMember($data['id'])->setGrossSalary($data['amount'])->setScheduleDate($data['schedule_date'])->update();
            }
        });
        return true;
    }
}