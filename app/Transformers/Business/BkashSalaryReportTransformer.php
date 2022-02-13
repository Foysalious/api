<?php namespace App\Transformers\Business;

use Sheba\Dal\BusinessMemberBkashInfo\BusinessMemberBkashInfo;
use Sheba\Dal\PayrollComponent\Components;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\Payslip\Payslip;
use App\Models\BusinessMember;
use App\Models\Profile;

class BkashSalaryReportTransformer extends TransformerAbstract
{
    const GROSS_SALARY = 'gross_salary';
    private $grossSalary;

    /**
     * @param Payslip $payslip
     * @return array
     */
    public function transform(Payslip $payslip)
    {
        /** @var BusinessMember $business_member */
        $business_member = $payslip->businessMember;
        /** @var Profile $profile */
        $profile = $business_member->profile();

        /** @var BusinessMemberBkashInfo $bkash_info */
        $bkash_info = $business_member->bkashInfos->last();

        $salary_breakdown = $payslip->salaryBreakdown();
        $this->getGrossBreakdown($salary_breakdown);

        return [
            'id' => $payslip->id,
            'business_member_id' => $business_member->id,
            'employee_id' => $business_member->employee_id,
            'name' => $profile->name,
            'account_no' => $bkash_info ? $bkash_info->account_no : null,
            'net_payable' => $this->getTotal($salary_breakdown)
        ];
    }

    /**
     * @param $salary_breakdown
     * @return float|int
     */
    private function getTotal($salary_breakdown)
    {
        $addition = 0;
        $deduction = 0;
        try {
            foreach ($salary_breakdown['payroll_component'] as $component_type => $component_breakdown) {
                if ($component_type == Type::ADDITION) {
                    foreach ($component_breakdown as $component_value) {
                        $addition += $component_value;
                    }
                }

                if ($component_type == Type::DEDUCTION) {
                    foreach ($component_breakdown as $component_value) {
                        $deduction += $component_value;
                    }
                }
            }
            return floatValFormat(($this->grossSalary + $addition) - $deduction);
        } catch (\Throwable $e) {
            return 0;
        }

    }

    /**
     * @param $salary_breakdown
     * @return array
     */
    private function getGrossBreakdown($salary_breakdown)
    {
        $gross_salary_breakdown = $salary_breakdown['gross_salary_breakdown'];

        $final_data = [];
        foreach ($gross_salary_breakdown as $component => $component_value) {
            if ($component == self::GROSS_SALARY) {
                $this->grossSalary = floatValFormat($component_value);
                continue;
            }
            $final_data[] = $this->componentBreakdown($component, $component_value, Type::GROSS);
        }

        return $final_data;
    }

    /**
     * @param $component
     * @param $component_value
     * @param $type
     * @return array
     */
    private function componentBreakdown($component, $component_value, $type)
    {
        $component_title = Components::getComponents($component)['value'];
        return [
            'key' => $component,
            'name' => $component_title ? $component_title : ucwords(implode(" ", explode("_", $component))),
            'value' => $component_value,
            'type' => $type
        ];
    }
}