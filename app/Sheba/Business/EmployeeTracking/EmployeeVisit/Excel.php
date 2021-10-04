<?php namespace Sheba\Business\EmployeeTracking\EmployeeVisit;

use Excel as EmployeeVisitExcel;
use Carbon\Carbon;

class Excel
{

    private $employeesVisitData;
    private $data = [];

    public function setEmployeeVisitData(array $employee_visit_data)
    {
        $this->employeesVisitData = $employee_visit_data;
        return $this;
    }

    public function get()
    {
        $header = $this->getHeaders();
        $this->makeData();
        $file_name = 'Employee_visit_report_'. Carbon::now()->timestamp;
        EmployeeVisitExcel::create($file_name, function ($excel) use ($header) {
            $excel->sheet('data', function ($sheet) use ($header) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell('A1:F1', function ($cells) {
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
        foreach ($this->employeesVisitData as $visit) {
            array_push($this->data, [
                'schedule_date' => $visit['schedule_date'],
                'employee_name' => $visit['profile']['name'],
                'department' => $visit['profile']['department'],
                'title' => $visit['title'],
                'description' => $visit['description'],
                'status' => $visit['status'],
            ]);
        }

    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Visit Date', 'Employee Name', 'Department', 'Title', 'Description', 'Status',
        ];
    }
}