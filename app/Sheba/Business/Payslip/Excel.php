<?php namespace App\Sheba\Business\Payslip;

use Excel as Payslip;

class Excel
{
    private $data=[];
    private $payslipData;
    private $name;

    public function setPayslipData(array $payslip_data)
    {
        $this->payslipData = $payslip_data;
        return $this;
    }

    public function setPayslipName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        $file_name = $this->name;
        Payslip::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:G1', function ($cells) {
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
        foreach ($this->payslipData as $payslip) {
            array_push($this->data, [
                'employee_id'   => $payslip['employee_id'],
                'employee_name'          => $payslip['employee_name'],
                'department'          => $payslip['department'],
                'gross_salary'          => $payslip['gross_salary'],
                'addition'              => $payslip['addition'],
                'deduction'              => $payslip['deduction'],
                'net_payable'          => $payslip['net_payable'],
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Gross Salary', 'Addition', 'Deduction', 'Net Payable'];
    }

}
