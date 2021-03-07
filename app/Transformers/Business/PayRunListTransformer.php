<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;
use Sheba\Dal\Payslip\Payslip;

class PayRunListTransformer extends TransformerAbstract
{
    private $grossSalary;

    public function transform(Payslip $payslip)
    {
        $this->grossSalary = $this->getGrossSalary($payslip->businessMember);
        $business_member = $payslip->businessMember;
        $department = $business_member->department();
        return [
            'id' =>  $payslip->id,
            'business_member_id' => $payslip->business_member_id,
            'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
            'employee_name' => $business_member->profile()->name,
            'department' => $department ? $department->name : 'N/A',
            'schedule_date' => Carbon::parse($payslip->schedule_date)->format('Y-m-d'),
            'gross_salary' => floatValFormat($this->grossSalary),
            'addition' => $this->getTotal($payslip,Type::ADDITION),
            'deduction' => $this->getTotal($payslip,Type::DEDUCTION),
            'net_payable' => $this->getTotal($payslip,'net_payable'),
            'components' => $this->getComponents($business_member),
            'gross_salary_breakdown' => $this->getGrossBreakdown($payslip),
            'addition_breakdown' => $this->getComponentBreakdown($payslip,Type::ADDITION),
            'deduction_breakdown' => $this->getComponentBreakdown($payslip,Type::DEDUCTION),
        ];
    }

    private function getGrossSalary(BusinessMember $business_member)
    {
        return $business_member->salary ? $business_member->salary->gross_salary : 0;
    }

    private function getTotal($payslip, $type)
    {
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1);
        $addition = 0;
        $deduction = 0;
        foreach ($salary_breakdown['payroll_component'] as $key => $payroll_component) {
            if ($key == Type::ADDITION) {
                foreach ($payroll_component as $component) {
                    $addition += $component;
                }
            }

            if ($key == Type::DEDUCTION) {
                foreach ($payroll_component as $component) {
                    $deduction += $component;
                }
            }
        }
        $net_payable = floatValFormat(($this->grossSalary + $addition) - $deduction);
        if ($type == 'net_payable') return $net_payable;
        if ($type == Type::ADDITION) return $addition;
        if ($type == Type::DEDUCTION) return $deduction;
    }

    private function getGrossBreakdown($payslip)
    {
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1)['gross_salary_breakdown'];

        $final_data  = [];
        foreach ($salary_breakdown as $key => $component) {
            if ($key == 'gross_salary') continue;
            $name = Components::getComponents($key)['value'];
            array_push($final_data, [
                'key' => $key,
                'name' => $name ? $name : ucwords(implode(" ", explode("_",$key))),
                'value' => $component
            ]);
        }

        return $final_data;
    }

    private function getComponentBreakdown($payslip, $type)
    {
        $salary_breakdown = json_decode($payslip->salary_breakdown, 1)['payroll_component'];
        $final_data  = [];
        foreach ($salary_breakdown as $key => $payroll_component) {
            if ($key == $type) {
                foreach ($payroll_component as $item => $component) {
                    $name = Components::getComponents($item)['value'];
                    array_push($final_data, [
                        'key' => $item,
                        'name' => $name ? $name : ucwords(implode(" ", explode("_",$item))),
                        'value' => $component
                    ]);
                }
            }
        }
        return $final_data;
    }

    private function getComponents($business_member)
    {
        $payroll_components = $business_member->business->payrollSetting->components->whereIn('type',[Type::ADDITION, Type::DEDUCTION]);
        $final_data = [];
        foreach ($payroll_components as $key => $payroll_component) {
            array_push($final_data, [
                'key' => $payroll_component->name,
                'title' => $payroll_component->is_default ? Components::getComponents($payroll_component->name)['value'] : ucwords(implode(" ", explode("_",$payroll_component->name))),
                'type' => $payroll_component->type
            ]);
        }
        return $final_data;
    }
}
