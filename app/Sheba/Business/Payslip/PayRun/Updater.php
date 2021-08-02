<?php namespace Sheba\Business\Payslip\PayRun;

use App\Jobs\Business\SendPayslipDisburseNotificationToEmployee;
use App\Jobs\Business\SendPayslipDisbursePushNotificationToEmployee;
use App\Sheba\Business\PayrollComponent\Components\GrossSalaryBreakdownCalculate;
use App\Sheba\Business\Salary\Requester as SalaryRequester;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\PayrollComponentRepository;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Business\Payslip\Updater as PayslipUpdater;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Updater
{
    private $payrunData;
    private $salaryRequester;
    private $salaryRepository;
    private $managerMember;
    private $payslipUpdater;
    private $payslipRepository;
    private $scheduleDate;
    private $business;
    private $businessMemberIds;
    private $payrollComponentRepository;
    /** @var GrossSalaryBreakdownCalculate $grossSalaryBreakdownCalculate */
    private $grossSalaryBreakdownCalculate;
    private $businessMemberRepository;


    /**
     * Updater constructor.
     * @param SalaryRequester $salary_requester
     * @param SalaryRepository $salary_repository
     * @param PayslipUpdater $payslip_updater
     * @param PayslipRepository $payslip_repository
     */
    public function __construct(SalaryRequester $salary_requester, SalaryRepository $salary_repository, PayslipUpdater $payslip_updater, PayslipRepository $payslip_repository)
    {
        $this->salaryRequester = $salary_requester;
        $this->salaryRepository = $salary_repository;
        $this->payslipUpdater = $payslip_updater;
        $this->payslipRepository = $payslip_repository;
        $this->payrollComponentRepository = app(PayrollComponentRepository::class);
        $this->grossSalaryBreakdownCalculate = app(GrossSalaryBreakdownCalculate::class);
        $this->businessMemberRepository = app(BusinessMemberRepositoryInterface::class);
    }

    public function setData($data)
    {
        $this->payrunData = json_decode($data, 1);
        return $this;
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        $this->businessMemberIds = $this->business->getAccessibleBusinessMember()->pluck('id')->toArray();
        return $this;
    }

    public function setScheduleDate($schedule_date)
    {
        $this->scheduleDate = $schedule_date;
        return $this;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    /**
     * @return bool
     */
    public function update()
    {
        DB::transaction(function () {
            foreach ($this->payrunData as $data) {
                $grossBreakdown = null;
                $business_member = $this->businessMemberRepository->find($data['id']);
                $previous_salary = $business_member->salary ? $business_member->salary->gross_salary : 0;
                if ($previous_salary != $data['amount']) $grossBreakdown = $this->createGrossBreakdown($business_member, $data['amount']);
                $this->salaryRequester->setBusinessMember($business_member)->setGrossSalary($data['amount'])->setBreakdownPercentage($grossBreakdown)->setManagerMember($this->managerMember)->createOrUpdate();
                $this->payslipUpdater->setBusinessMember($business_member)->setGrossSalary($data['amount'])->setScheduleDate($data['schedule_date'])->setAddition($data['addition'])->setDeduction($data['deduction'])->update();
            }
        });
        return true;
    }

    public function disburse()
    {
        $payslips = $this->payslipRepository->getPaySlipByStatus($this->businessMemberIds, Status::PENDING)->where('schedule_date', 'like', '%' . $this->scheduleDate . '%');

        DB::transaction(function () use ($payslips) {
            $payslips->update(['status' => Status::DISBURSED]);
        });
        $this->sendNotifications($payslips);
        return true;
    }

    /**
     * @param $business_member
     * @param $gross_salary
     * @return false|string
     */
    private function createGrossBreakdown($business_member, $gross_salary)
    {
        $gross_salary_breakdown_percentage = $this->grossSalaryBreakdownCalculate->payslipComponentPercentageBreakdown($business_member);
        $data = [];
        foreach ($gross_salary_breakdown_percentage as $component_name => $component_value) {
            $component = $this->payrollComponentRepository->where('name', $component_name)->where('type', Type::GROSS)->where('is_active', 1)->where('target_type', TargetType::EMPLOYEE)->where('target_id', $business_member->id)->first();
            if (!$component) $component = $this->payrollComponentRepository->where('name', $component_name)->where('type', Type::GROSS)->where('target_type', TargetType::GENERAL)->where(function ($query) {
                return $query->where('is_default', 1)->orWhere('is_active', 1);
            })->first();

            $percentage = floatval(json_decode($component->setting, 1)['percentage']);
            array_push($data, [
                'id' => $component->id,
                'name' => $component_name,
                'title' => $component->is_default ? Components::getComponents($component_name)['value'] : $component->value,
                "value" => $percentage,
                "amount" => ((floatval($gross_salary) * $percentage) / 100),
            ]);
        }
        return json_encode($data);
    }

    public function sendNotifications($payslips)
    {
        $payslips = $payslips->get();
        foreach ($payslips as $payslip) {
            $business_member = $this->businessMemberRepository->find($payslip->business_member_id);
            dispatch(new SendPayslipDisburseNotificationToEmployee($business_member, $payslip));
            dispatch(new SendPayslipDisbursePushNotificationToEmployee($business_member, $payslip));
        }
    }
}
