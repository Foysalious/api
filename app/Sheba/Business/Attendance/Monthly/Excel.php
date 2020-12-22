<?php namespace Sheba\Business\Attendance\Monthly;

use Carbon\Carbon;
use Excel as MonthlyExcel;

class Excel
{
    private $monthlyData;
    private $data=[];


    public function setMonthlyData(array $monthly_data)
    {
        $this->monthlyData = $monthly_data;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        $file_name = 'Monthly_attendance_report';
        MonthlyExcel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:L1', function ($cells) {
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
        foreach ($this->monthlyData as $employee) {
            array_push($this->data, [
                'employee_id'   => $employee['employee_id'],
                'name'          => $employee['member']['name'],
                'dept'          => $employee['department']['name'],
                'working_days'  => $employee['attendance']['working_days'],
                'present'       => $employee['attendance']['present'],
                'on_time'       => $employee['attendance']['on_time'],
                'late'          => $employee['attendance']['late'],
                'left_timely'   => $employee['attendance']['left_timely'],
                'left_early'    => $employee['attendance']['left_early'],
                'left_early_note' => $employee['attendance']['left_early_note'],
                'on_leave'      => $employee['attendance']['on_leave'],
                'absent'        => $employee['attendance']['absent']
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Working Days', 'Present', 'On time', 'Late', 'Left Timely', 'Left early', 'left_early_note', 'On leave','Absent'];
    }
}
