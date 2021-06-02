<?php namespace Sheba\Business\Payslip\PayRun;

use App\Jobs\Business\SendPayslipDisburseNotificationToEmployee;
use App\Jobs\Business\SendPayslipDisbursePushNotificationToEmployee;
use App\Sheba\Business\Salary\Requester as SalaryRequester;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Payslip\PayslipRepository;
use Sheba\Dal\Payslip\Status;
use Sheba\Dal\Salary\SalaryRepository;
use Sheba\Business\Payslip\Updater as PayslipUpdater;

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
                $this->salaryRequester->setBusinessMember($data['id'])->setGrossSalary($data['amount'])->setManagerMember($this->managerMember)->createOrUpdate();
                $this->payslipUpdater->setBusinessMember($data['id'])->setGrossSalary($data['amount'])->setScheduleDate($data['schedule_date'])->setAddition($data['addition'])->setDeduction($data['deduction'])->update();
            }
        });
        return true;
    }

    /**
     * @return bool
     */
    public function disburse()
    {
        DB::transaction(function () {
            $this->payslipRepository->getPaySlipByStatus($this->businessMemberIds, Status::PENDING)->where('schedule_date', 'like', '%' . $this->scheduleDate . '%')->update(['status' => Status::DISBURSED]);
        });
        $this->sendNotifications();
        return true;
    }

    public function sendNotifications()
    {
        $business_members = $this->business->getAccessibleBusinessMember()->get();
        foreach ($business_members as $business_member) {
            $payslip = $this->payslipRepository->where('business_member_id', $business_member->id)->where('status', Status::DISBURSED)->where('schedule_date', 'like', '%' . $this->scheduleDate . '%')->first();
            if ($payslip) {
                dispatch(new SendPayslipDisburseNotificationToEmployee($business_member, $payslip));
                dispatch(new SendPayslipDisbursePushNotificationToEmployee($business_member, $payslip));
            }
        }
    }
}
