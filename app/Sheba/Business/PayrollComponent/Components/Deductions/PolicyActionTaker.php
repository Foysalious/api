<?php namespace App\Sheba\Business\PayrollComponent\Components\Deductions;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\PayrollSetting\PayrollCommonCalculation;
use App\Sheba\Business\PayrollSetting\PayrollConstGetter;
use Sheba\Business\Prorate\Creator as ProrateCreator;
use Sheba\Business\Prorate\Requester as ProrateRequester;
use Sheba\Business\Prorate\Updater as ProrateUpdater;
use Sheba\Dal\BusinessMemberLeaveType\Contract as BusinessMemberLeaveTypeInterface;
use Sheba\Dal\OfficePolicy\Type;
use Sheba\Dal\OfficePolicyRule\ActionType;
use Sheba\Dal\PayrollComponent\TargetType;
use Sheba\Dal\PayrollComponent\Type as PayrollComponentType;

class PolicyActionTaker
{
    use PayrollCommonCalculation;
    /*** @var Business */
    private $business;
    private $penaltyDays;
    private $totalWorkingDays;
    private $policyRules;
    /*** @var BusinessMember */
    private $businessMember;
    private $businessMemberLeaveTypeRepo;
    private $prorateRequester;
    /*** @var ProrateCreator $prorateCreator */
    private $prorateCreator;
    /*** @var ProrateUpdater $prorateUpdater */
    private $prorateUpdater;
    private $payrollSetting;
    private $additionBreakdown;
    private $policyType;
    private $isUnpaidLeavePolicyEnable;
    private $businessMemberSalay;

    public function __construct()
    {
        $this->businessMemberLeaveTypeRepo = app(BusinessMemberLeaveTypeInterface::class);
        $this->prorateRequester = app(ProrateRequester::class);
        $this->prorateCreator = app(ProrateCreator::class);
        $this->prorateUpdater = app(ProrateUpdater::class);
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->businessMemberSalay = $business_member->salary ? floatValFormat($this->businessMember->salary->gross_salary) : 0;
        return $this;
    }

    public function setPayrollSetting($payroll_setting)
    {
        $this->payrollSetting = $payroll_setting;
        return $this;
    }

    public function setPolicyType($policy_type)
    {
        $this->policyType = $policy_type;
        return $this;
    }

    public function setAdditionBreakdown($addition_breakdown)
    {
        $this->additionBreakdown = $addition_breakdown;
        return $this;
    }

    public function setPenaltyDays($penalty_days)
    {
        $this->penaltyDays = $penalty_days;
        return $this;
    }

    public function setTotalWorkingDays($total_working_days)
    {
        $this->totalWorkingDays = $total_working_days;
        return $this;
    }

    public function setIsUnpaidLeaveEnable($is_unpaid_leave_policy_enable)
    {
        $this->isUnpaidLeavePolicyEnable = $is_unpaid_leave_policy_enable;
        return $this;
    }

    public function takeAction()
    {
        if ($this->policyType === Type::UNPAID_LEAVE && !$this->isUnpaidLeavePolicyEnable) return $this->totalPenaltyAmountByOneWorkingDay($this->oneWorkingDayAmount($this->businessMemberSalay,  floatValFormat($this->totalWorkingDays)), $this->penaltyDays);
        $this->policyRules = $this->business->policy()->where('policy_type', $this->policyType)->where(function ($query) {
            $query->where('from_days', '<=', $this->penaltyDays);
            $query->where('to_days', '>=', $this->penaltyDays);
        })->first();

        return $this->rulesPolicyCalculation();
    }

    private function rulesPolicyCalculation()
    {
        $policy_total = 0;

        if (!$this->policyRules) return $policy_total;
        $action = $this->policyRules->action;
        if ($action === ActionType::NO_PENALTY) return $policy_total;
        if ($action === ActionType::CASH_PENALTY) return floatValFormat($this->policyRules->penalty_amount);
        $business_member_salary = $this->businessMember->salary ? floatValFormat($this->businessMember->salary->gross_salary) : 0;
        if ($action === ActionType::LEAVE_ADJUSTMENT){
            $penalty_type = intval($this->policyRules->penalty_type);
            $penalty_amount = floatValFormat($this->policyRules->penalty_amount);
            $existing_prorate = $this->businessMemberLeaveTypeRepo->where('business_member_id', $this->businessMember->id)->where('leave_type_id', $penalty_type)->first();
            $total_leave_type_days = $existing_prorate ? floatValFormat($existing_prorate->total_days) : floatValFormat($this->business->leaveTypes->find($penalty_type)->total_days);
            if ($total_leave_type_days < $penalty_amount) {
                $one_working_day_amount = $this->oneWorkingDayAmount($business_member_salary,  floatValFormat($this->totalWorkingDays));
                return $this->totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount);
            }
            $total_leave_type_days_after_penalty = $total_leave_type_days - $penalty_amount;
            $this->prorateRequester->setBusinessMemberIds([$this->businessMember->id])
                ->setLeaveTypeId($penalty_type)
                ->setTotalDays($total_leave_type_days_after_penalty)
                ->setNote(PayrollConstGetter::LEAVE_PRORATE_NOTE_FOR_POLICY_ACTION);
            if ($existing_prorate) $this->prorateUpdater->setRequester($this->prorateRequester)->setBusinessMemberLeaveType($existing_prorate)->update();
            else $this->prorateCreator->setRequester($this->prorateRequester)->create();

            return $policy_total;
        }
        if ($action === ActionType::SALARY_ADJUSTMENT) {
            $penalty_type = $this->policyRules->penalty_type;
            $penalty_amount = floatValFormat($this->policyRules->penalty_amount);
            $gross_component = $this->payrollSetting->components->where('name', $penalty_type)->where('type', 'gross')->where('target_type', 'employee')->where('target_id', $this->businessMember)->first();
            if (!$gross_component) $gross_component = $this->payrollSetting->components()->where('name', $penalty_type)->where('type', PayrollComponentType::GROSS)->where(function($query) {
                return $query->where('target_type', null)->orWhere('target_type', TargetType::GENERAL);
            })->first();
            if ($gross_component) {
                $percentage = floatValFormat(json_decode($gross_component->setting, 1)['percentage']);
                $amount = ($business_member_salary * $percentage) / 100;
                $one_working_day_amount = $this->oneWorkingDayAmount($amount,  floatValFormat($this->totalWorkingDays));
                return $this->totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount);
            }
            $addition_component = $this->payrollSetting->components->where('name', $penalty_type)->where('type', 'addition')->first();
            if ($addition_component) {
                $amount = $this->additionBreakdown['addition'][$penalty_type];
                $one_working_day_amount = $this->oneWorkingDayAmount($amount,  floatValFormat($this->totalWorkingDays));
                return ($one_working_day_amount * $penalty_amount);
            }
            if (!$gross_component && !$addition_component){
                $one_working_day_amount = $this->oneWorkingDayAmount($business_member_salary,  floatValFormat($this->totalWorkingDays));
                return $this->totalPenaltyAmountByOneWorkingDay($one_working_day_amount, $penalty_amount);
            }
        }
        return $policy_total;
    }
}