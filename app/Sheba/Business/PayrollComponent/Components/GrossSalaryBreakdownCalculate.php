<?php namespace App\Sheba\Business\PayrollComponent\Components;

use Sheba\Business\PayrollComponent\Components\MedicalAllowance;
use Sheba\Business\PayrollComponent\Components\BasicSalary;
use Sheba\Business\PayrollComponent\Components\Conveyance;
use Sheba\Business\PayrollComponent\Components\HouseRent;
use Sheba\Dal\PayrollComponent\PayrollComponent;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Dal\PayrollComponent\Type;

class GrossSalaryBreakdownCalculate
{
    private $componentPercentage;
    private $totalAmountPerComponent;
    private $grossSalaryBreakdownWithTotalAmount;

    public function __construct()
    {
        $this->componentPercentage = new GrossSalaryComponent();
        $this->totalAmountPerComponent = new GrossSalaryComponent();
        $this->grossSalaryBreakdownWithTotalAmount = [];
    }

    /**
     * @param $payroll_setting
     * @return GrossSalaryComponent
     */
    public function componentPercentageBreakdown($payroll_setting)
    {
        /** @var PayrollComponent $payroll_components */
        $payroll_components = $payroll_setting->components()->where('type', Type::GROSS)->get();
        foreach ($payroll_components as $payroll_component) {
            if ($payroll_component->name == Components::BASIC_SALARY) {
                $this->componentPercentage->basicSalary = (new BasicSalary($payroll_component))->getPercentage();
            }
            if ($payroll_component->name == Components::HOUSE_RENT) {
                $this->componentPercentage->houseRent = (new HouseRent($payroll_component))->getPercentage();
            }
            if ($payroll_component->name == Components::MEDICAL_ALLOWANCE) {
                $this->componentPercentage->medicalAllowance = (new MedicalAllowance($payroll_component))->getPercentage();
            }
            if ($payroll_component->name == Components::CONVEYANCE) {
                $this->componentPercentage->conveyance = (new Conveyance($payroll_component))->getPercentage();
            }
        }
        return $this->componentPercentage;
    }


    /**
     * @param float $gross_salary
     * @return GrossSalaryComponent
     */
    public function totalAmountPerComponent($gross_salary = 0.0)
    {
        $this->totalAmountPerComponent->basicSalary = floatValFormat(($gross_salary * $this->componentPercentage->basicSalary) / 100);
        $this->totalAmountPerComponent->houseRent = floatValFormat(($gross_salary * $this->componentPercentage->houseRent) / 100);
        $this->totalAmountPerComponent->medicalAllowance = floatValFormat(($gross_salary * $this->componentPercentage->medicalAllowance) / 100);
        $this->totalAmountPerComponent->conveyance = floatValFormat(($gross_salary * $this->componentPercentage->conveyance) / 100);
        $this->totalAmountPerComponent->grossSalary = floatValFormat($gross_salary);
        return $this->totalAmountPerComponent;
    }

    /**
     * @return array[]
     */
    public function totalAmountPerComponentFormatted()
    {
        $this->grossSalaryBreakdownWithTotalAmount = [
            'gross_salary_breakdown' => [
                'basic_salary' => $this->totalAmountPerComponent->basicSalary,
                'house_rent' => $this->totalAmountPerComponent->houseRent,
                'medical_allowance' => $this->totalAmountPerComponent->medicalAllowance,
                'conveyance' => $this->totalAmountPerComponent->conveyance,
                'gross_salary' => $this->totalAmountPerComponent->grossSalary,
            ]
        ];
        return $this->grossSalaryBreakdownWithTotalAmount;
    }
}
