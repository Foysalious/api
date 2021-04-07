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
        $x = 1;
        foreach ($this->payslip as $payslip) {
            $business_member_data = [
                    'serial_no' => sprintf("%03d", $x++),
                    'business_member_id' => $payslip['business_member_id'],
                    'employee_name' => $payslip['employee_name'],
                    'employee_id' => $payslip['employee_id'],
                    'department' => $payslip['department'] ? $payslip['department'] : 'N/A',
                    'schedule_date' => $payslip['schedule_date'],
                    'gross_salary' => $payslip['gross_salary'],
                ] + $this->getComponents($payslip);
            array_push($this->data, $business_member_data);
        }
    }

    private function getHeaders()
    {
        $header = ['Serial', 'Business Member ID', 'Employee Name', 'Employee ID', 'Department', 'Schedule Date', 'Gross Salary'];
        $this->maxCell = 7;
        foreach ($this->payrollComponents as $component) {
            if ($component->is_default) $header_title = Components::getComponents($component->name)['value'];
            $header_title = ucwords(implode(" ", explode("_",$component->name)));

            $header[] = $header_title.':'.$component->type;
            $this->maxCell++;
        }
        return $header;
    }

    private function getComponents($payslip)
    {
        $additional_business_components = $this->payrollComponents->where('type', 'addition')->pluck('name')->toArray();
        $deductional_business_components = $this->payrollComponents->where('type', 'deduction')->pluck('name')->toArray();
        $data = [];
        foreach ($additional_business_components as $components) {
            foreach ($payslip['addition_breakdown'] as $addition_breakdown) {
                if (!in_array($addition_breakdown['key'], $additional_business_components)) $data[$components] = 0;
                else $data[$addition_breakdown['key']] = $addition_breakdown['value'];
            }
        }
        foreach ($deductional_business_components as $components) {
            foreach ($payslip['deduction_breakdown'] as $deduction_breakdown) {
                if (!in_array($deduction_breakdown['key'], $deductional_business_components)) $data[$components] = 0;
                else $data[$deduction_breakdown['key']] = $deduction_breakdown['value'];
            }
        }
        return $data;
    }
}
