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
        $file_name = 'Employee_visit_report_' . Carbon::now()->timestamp;
        EmployeeVisitExcel::create($file_name, function ($excel) use ($header) {
            $excel->sheet('data', function ($sheet) use ($header) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($header);
                $sheet->freezeFirstRow();
                $sheet->cell('A1:S1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );


                $sheet->setAutoSize(array(
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'J', 'L', 'N', 'O', 'P', 'Q', 'S'
                ));
                $sheet->getColumnDimension('H')->setWidth(35);
                $sheet->getColumnDimension('I')->setWidth(30);
                $sheet->getColumnDimension('K')->setWidth(30);
                $sheet->getColumnDimension('M')->setWidth(30);
                $sheet->getColumnDimension('R')->setWidth(30);

                $sheet->setAutoSize(false);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->employeesVisitData as $visit) {
            array_push($this->data, [
                    'schedule_date' => $visit['schedule_date'],
                    'employee_id' => $visit['profile']['employee_id'],
                    'employee_name' => $visit['profile']['name'],
                    'department' => $visit['profile']['department'],
                    'title' => $visit['title'],
                    'description' => $visit['description'],
                    'status' => $visit['status'],
                    'picture' => $visit['photos'],
                    'started_location' => $visit['visit_started_location'],
                    'started_time' => $visit['visit_started_date'],
                    'reached_location' => $visit['visit_reached_location'],
                    'reached_date' => $visit['visit_reached_date'],
                    'completed_location' => $visit['visit_completed_location'],
                    'complete_date' => $visit['visit_complete_date'],
                    'total_hours' => $visit['total_hours'],
                    'cancelled_at' => $visit['visit_cancelled_at'],
                    'cancelled_note' => $visit['visit_cancelled_note'],
                    'rescheduled_at' => $visit['visit_reschedule_dates'],
                    'rescheduled_notes' => $visit['visit_reschedule_notes'],
                ] + $visit['all_notes']);
        }

    }

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Visit Date', 'Employee Id', 'Employee Name', 'Department', 'Title', 'Description', 'Status',
            'Pictures', 'Start Location', 'Start Time', 'Reached Location', 'Reached Time', 'End Location', 'End Time',
            'Total Visit Time', 'Cancelled At', 'Cancelled Reason', 'Rescheduled At', 'Rescheduled Reason'
        ];
    }
}