<?php namespace Sheba\Business\Attendance\Daily;

use Excel;

class DailyExcel
{
    private $dailyData;
    private $data = [];
    private $date;
    private $employeeId;
    private $employeeName;
    private $department;
    private $status;
    private $checkInTime;
    private $checkInStatus;
    private $checkInLocation;
    private $checkInAddress;
    private $checkOutTime;
    private $checkOutStatus;
    private $checkOutLocation;
    private $checkOutAddress;
    private $totalHours;
    private $lateNote;
    private $leftEarlyNote;

    private function initializeData()
    {
        $this->employeeId = null;
        $this->employeeName = null;
        $this->department = null;
        $this->status = null;
        $this->checkInTime = '-';
        $this->checkInStatus = '-';
        $this->checkInLocation = '-';
        $this->checkInAddress = '-';
        $this->checkOutTime = '-';
        $this->checkOutStatus = '-';
        $this->checkOutLocation = '-';
        $this->checkOutAddress = '-';
        $this->totalHours = '-';
        $this->lateNote = '-';
        $this->leftEarlyNote = '-';
    }

    public function setData(array $daily_data)
    {
        $this->dailyData = $daily_data;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function download()
    {
        $this->makeData();
        $file_name = 'Daily_attendance_report';
        Excel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:O1', function ($cells) {
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
        foreach ($this->dailyData as $attendance) {
            $this->initializeData();
            if (!is_null($attendance['check_in']) && !$attendance['is_absent']) {
                if ($attendance['is_half_day_leave']) {
                    $this->status = "On leave: half day";
                } else {
                    $this->status = 'Present';
                }

                $this->checkInTime = $attendance['check_in']['checkin_time'];
                if ($attendance['check_in']['status'] == 'late') {
                    $this->checkInStatus = "Late";
                }
                if ($attendance['check_in']['status'] == 'on_time') {
                    $this->checkInStatus = "On time";
                }
                if ($attendance['check_in']['is_remote']) {
                    $this->checkInLocation = "Remote";
                } else {
                    $this->checkInLocation = "Office IP";
                }

                $this->checkInAddress = $attendance['check_in']['address'];
                if (!is_null($attendance['check_out'])) {
                    $this->checkOutTime = $attendance['check_out']['checkout_time'];

                    if ($attendance['check_out']['status'] == 'left_early') {
                        $this->checkOutStatus = 'Left early';
                    }
                    if ($attendance['check_out']['status'] == 'left_timely') {
                        $this->checkOutStatus = 'Left timely';
                    }
                    if ($attendance['check_out']['is_remote']) {
                        $this->checkOutLocation = "Remote";
                    } else {
                        $this->checkOutLocation = "Office IP";
                    }
                    $this->checkOutAddress = $attendance['check_out']['address'];
                }

                $this->totalHours = $attendance['active_hours'];
                $this->lateNote = $attendance['check_in']['note'];
                $this->leftEarlyNote = $attendance['check_out']['note'];
            }

            if ($attendance['is_absent']) {
                $this->status = "Absent";
            }
            if ($attendance['is_on_leave']) {
                if (!$attendance['is_half_day_leave']) {
                    $this->status = "On leave: full day";
                } else {
                    $this->status = "On leave: half day";
                }
            }

            array_push($this->data, [
                'date' => $attendance['date'] ? $attendance['date'] : $this->date,
                'employee_id' => $attendance['employee_id'],
                'employee_name' => $attendance['member']['name'],
                'department' => $attendance['department']['name'],

                'status' => $this->status,
                'check_in_time' => $this->checkInTime,
                'check_in_status' => $this->checkInStatus,
                'check_in_location' => $this->checkInLocation,
                'check_in_address' => $this->checkInAddress,
                'check_out_time' => $this->checkOutTime,
                'check_out_status' => $this->checkOutStatus,
                'check_out_location' => $this->checkOutLocation,
                'check_out_address' => $this->checkOutAddress,

                'total_hours' => $this->totalHours,
                'late_check_in_note' => $this->lateNote,
                'left_early_note' => $this->leftEarlyNote,
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Date', 'Employee ID', 'Employee Name', 'Department',
            'Status', 'Check in time', 'Check in status', 'Check in location',
            'Check in address', 'Check out time', 'Check out status',
            'Check out location', 'Check out address', 'Total Hours', 'Late check in note', 'Left early note'];
    }
}
