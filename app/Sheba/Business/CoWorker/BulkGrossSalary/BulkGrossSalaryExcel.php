<?php namespace App\Sheba\Business\CoWorker\BulkGrossSalary;

use Carbon\Carbon;
use Excel;

class BulkGrossSalaryExcel
{
    private $employeeData;

    public function setEmployeeData(array $employee_data)
    {
        $this->employeeData = $employee_data;
        return $this;
    }

    public function download()
    {
        $six_digit_random_number = random_int(100000, 999999);
        $file_name = 'Coworker_Gross_Salary_Report_' . $six_digit_random_number . '_' . Carbon::now()->toDateTimeString();
        Excel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->makeData(), null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:C1', function ($cells) {
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
        $formatted_data = [];
        foreach ($this->employeeData as $employee_data) {
            $formatted_data [] = [
                'employee_name' => $employee_data['profile']['name'],
                'employee_email' => $employee_data['profile']['email'],
                'gross_salary' => $employee_data['gross_salary']
            ];
        }
        return $formatted_data;
    }

    private function getHeaders()
    {
        return ['Employee Name', 'Employee Email', 'Gross Salary'];
    }
}
