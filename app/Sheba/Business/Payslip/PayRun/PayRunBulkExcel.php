<?php namespace App\Sheba\Business\Payslip\PayRun;

use App\Models\Business;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Reports\ExcelHandler;
use Excel;

class PayRunBulkExcel
{
    private $excelHandler;
    private $data;
    private $business;
    private $businessMembers;
    private $payrollComponents;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMembers($business_members)
    {
        $this->businessMembers = $business_members;
        return $this;
    }

    public function setPayrollComponent($payroll_components)
    {
        $this->payrollComponents = $payroll_components;
        return $this;
    }

    public function get()
    {
        $header = $this->getHeaders();
        $this->makeData();
        $file_name = 'Pay_run_sample_excel';
        Excel::create($file_name, function ($excel) use ($header){
            $excel->sheet('data', function ($sheet) use ($header){
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell('A1:J1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->businessMembers->get() as $business_member) {
            $profile = $business_member->profile();
            $business_member_data = [
                'id' => $business_member->id,
                'employee_name' => $profile->name,
                'employee_id' => $profile->employee_id,
                'department' => $business_member->department() ? $business_member->department()->name : 'N/A',
                'gross_salary' => $business_member->salary ? $business_member->salary['gross_salary'] : 0,
            ] + $this->getComponents();
            array_push($this->data, $business_member_data);
        }
    }

    private function getHeaders()
    {
        $header = ['ID', 'Employee Name', 'Employee ID', 'Department', 'Gross Salary'];
        foreach ($this->payrollComponents as $component) {
            $component_value = Components::getComponents($component->name);
            $header[] = $component_value['value'];
        }
        return $header;
    }

    private function getComponents()
    {
        $data = [];
        foreach ($this->payrollComponents as $component) {
            $data[$component->name] = 0;
        }
        return $data;
    }
}
