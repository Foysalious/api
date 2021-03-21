<?php namespace App\Sheba\Business\Payslip\PayRun;

use App\Models\Business;
use PHPExcel_Cell;
use Sheba\Dal\PayrollComponent\Components;
use Sheba\Reports\ExcelHandler;
use Excel;

class PayRunBulkExcel
{
    private $excelHandler;
    private $data;
    private $business;
    private $payrollComponents;
    private $payslip;
    private $maxCell;

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

    public function setPayslips($payslip)
    {
        $this->payslip = $payslip;
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
        $max_bold_cell = 'A1:'.PHPExcel_Cell::stringFromColumnIndex($this->maxCell - 1).'1';
        Excel::create($file_name, function ($excel) use ($header, $max_bold_cell){
            $excel->sheet('data', function ($sheet) use ($header, $max_bold_cell){
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell($max_bold_cell, function ($cells) {
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
        foreach ($this->payslip as $payslip) {
            $business_member_data = [
                'id' => $payslip['business_member_id'],
                'employee_name' => $payslip['employee_name'],
                'employee_id' => $payslip['employee_id'],
                'department' => $payslip['department'] ? $payslip['department'] : 'N/A',
                'gross_salary' => $payslip['gross_salary'],
            ] + $this->getComponents();
            array_push($this->data, $business_member_data);
        }
    }

    private function getHeaders()
    {
        $header = ['ID', 'Employee Name', 'Employee ID', 'Department', 'Gross Salary'];
        $this->maxCell = 5;
        foreach ($this->payrollComponents as $component) {
            if ($component->is_default) $header_title = Components::getComponents($component->name)['value'];
            $header_title = ucwords(implode(" ", explode("_",$component->name)));

            $header[] = $header_title.':'.$component->type;
            $this->maxCell++;
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
