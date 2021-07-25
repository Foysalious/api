<?php namespace Sheba\Business\Attendance\Detail;

use Carbon\Carbon;
use Excel;

class DetailsExcel
{
    private $breakdownData;
    private $data = [];
    private $date;
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
    private $businessMember;
    private $department;
    private $profile;
    private $startDate;
    private $endDate;
    private $overtime;

    public function __construct()
    {
    }

    public function setBreakDownData(array $detailed_data)
    {
        $this->breakdownData = $detailed_data;
        return $this;
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        $this->profile = $this->businessMember->member->profile;
        $this->department = $this->businessMember->department();
        return $this;
    }

    public function setStartDate($start_date)
    {
        $this->startDate = $start_date;
        return $this;
    }

    public function setEndDate($end_date)
    {
        $this->endDate = $end_date;
        return $this;
    }

    public function setDepartment($department)
    {
        $this->department = $department;
        return $this;
    }

    public function download()
    {
        $this->makeData();

        $file_name = $this->businessMember->employee_id ?
            $this->profile->name . '_' . $this->department->name . '_' . $this->businessMember->employee_id :
            $this->profile->name . '_' . $this->department->name;

        $sheet_name = $this->startDate . ' - ' . $this->endDate;

        Excel::create($file_name, function ($excel) use ($sheet_name) {
            $excel->sheet($sheet_name, function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:M1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->breakdownData as $attendance) {
            $this->date = null;
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
            $this->overtime = '-';
            $this->lateNote = null;
            $this->leftEarlyNote = null;
            if (!$attendance['weekend_or_holiday_tag']) {
                if ($attendance['show_attendance'] == 1) {
                    $this->date = $attendance['date'];
                    $this->checkInOutLogics($attendance);
                    $this->status = 'Present';
                }
                if ($attendance['show_attendance'] == 0) {
                    if ($attendance['is_absent'] == 1) {
                        $this->date = $attendance['date'];
                        $this->status = 'Absent';
                    }
                }
            }
            if ($attendance['weekend_or_holiday_tag']) {
                $this->date = $attendance['date'];
                if ($attendance['show_attendance'] == 1) {
                    $this->checkInOutLogics($attendance);
                }
                if ($attendance['weekend_or_holiday_tag'] === 'weekend') {
                    $this->status = 'Weekend';
                } else if ($attendance['weekend_or_holiday_tag'] === 'holiday') {
                    $this->status = 'Holiday';
                } else if ($attendance['weekend_or_holiday_tag'] === 'full_day') {
                    $this->status = 'On leave: full day';
                } else if ($attendance['weekend_or_holiday_tag'] === 'first_half' || $attendance['weekend_or_holiday_tag'] === 'second_half') {
                    $this->status = "On leave: half day";
                }
            }
            array_push($this->data, [
                'date' => $this->date,
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
                'overtime' => $this->overtime,
                'late_check_in_note' => $this->lateNote,
                'left_early_note' => $this->leftEarlyNote,
            ]);
        }
    }

    private function getHeaders()
    {
        return ['Date', 'Status', 'Check in time', 'Check in status', 'Check in location',
            'Check in address', 'Check out time', 'Check out status',
            'Check out location', 'Check out address', 'Total Hours', 'Overtime in Minutes', 'Late check in note', 'Left early note'];
    }

    private function checkInOutLogics($attendance)
    {
        $attendance_check_in = $attendance['attendance']['check_in'];
        $attendance_check_out = $attendance['attendance']['check_out'];

        $this->checkInTime = $attendance_check_in['time'];
        if ($attendance_check_in['status'] === 'late') {
            $this->checkInStatus = 'Late';
        }
        if ($attendance_check_in['status'] === 'on_time') {
            $this->checkInStatus = 'On time';
        }
        if ($attendance_check_in['is_remote']) {
            $this->checkInLocation = "Remote";
        } else {
            $this->checkInLocation = "Office IP";
        }
        if ($attendance_check_in['address']) {
            $this->checkInAddress = $attendance_check_in['address'];
        }

        if (!is_null($attendance_check_out)) {
            $this->checkOutTime = $attendance_check_out['time'];

            if ($attendance_check_out['status'] === 'left_early') {
                $this->checkOutStatus = 'Left early';
            }

            if ($attendance_check_out['status'] === 'left_timely') {
                $this->checkOutStatus = 'Left timely';
            }

            if ($attendance_check_out['is_remote']) {
                $this->checkOutLocation = 'Remote';
            } else {
                $this->checkOutLocation = 'Office IP';
            }
            if ($attendance_check_out['address']) {
                $this->checkOutAddress = $attendance_check_out['address'];
            }
        }

        if ($attendance['attendance']['active_hours']) {
            $this->totalHours = $attendance['attendance']['active_hours'];
        }

        if ($attendance['attendance']['overtime_in_minutes']) {
            $this->overtime = $attendance['attendance']['overtime_in_minutes'];
        }

        $this->lateNote = $attendance['attendance']['late_note'];
        $this->leftEarlyNote = $attendance['attendance']['left_early_note'];
    }
}