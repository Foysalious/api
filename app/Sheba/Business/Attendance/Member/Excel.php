<?php namespace Sheba\Business\Attendance\Member;

use App\Sheba\Business\Attendance\Member\StatusPresenter;
use Sheba\Reports\ExcelHandler;

class Excel
{
    private $monthlyData;
    private $designation;
    private $department;
    private $member;
    private $excelHandler;
    private $statusPresenter;
    private $data;

    public function __construct(ExcelHandler $excelHandler, StatusPresenter $status_presenter)
    {
        $this->excelHandler = $excelHandler;
        $this->statusPresenter = $status_presenter;
        $this->data = [];
    }

    public function setMonthlyData(array $monthly_data)
    {
        $this->monthlyData = $monthly_data;
        return $this;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setDesignation($designation)
    {
        $this->designation = $designation;
        return $this;
    }

    public function setDepartment($department)
    {
        $this->department = $department;
        return $this;
    }


    public function get()
    {
        $this->makeData();
        return $this->excelHandler->setName('Employee Monthly Attendance')->createReport($this->data)->download();
    }

    private function isWeekendOrHoliday($attendance)
    {
        if ($attendance['weekend_or_holiday_tag']) return true;
        return false;
    }

    private function makeData()
    {
        foreach ($this->monthlyData as $attendance) {
            $date = null;
            $checkin_time = null;
            $checkout_time = null;
            $active_hours = null;
            $status = null;
            if (!$attendance['weekend_or_holiday_tag']) {
                if ($attendance['show_attendance'] == 1) {
                    $date = $attendance['date'];
                    $checkin_time = $attendance['attendance']['checkin_time'];
                    $checkout_time = $attendance['attendance']['checkout_time'];
                    $active_hours = $attendance['attendance']['staying_time_in_minutes'];
                    $status = $attendance['attendance']['status'];
                }
                if ($attendance['show_attendance'] == 0) {
                    if ($attendance['is_absent'] == 1) {
                        $date = $attendance['date'];
                        $status = 'absent';
                    }
                    if ($attendance['is_absent'] == 0) {
                        $date = $attendance['date'];
                    }
                }
            }
            if ($attendance['weekend_or_holiday_tag']) {
                if ($attendance['show_attendance'] == 0) {
                    $date = $attendance['date'];
                    $status = $attendance['weekend_or_holiday_tag'];
                }
                if ($attendance['show_attendance'] == 1) {
                    $date = $attendance['date'];
                    $checkin_time = $attendance['attendance']['checkin_time'];
                    $checkout_time = $attendance['attendance']['checkout_time'];
                    $active_hours = $attendance['attendance']['staying_time_in_minutes'];
                    $status = $attendance['attendance']['status'];
                }
            }
            array_push($this->data, [
                'member_id' => $this->member->id,
                'member_name' => $this->member->profile->name,
                'dept_name' => $this->department,
                'designation' => $this->designation,
                'date' => $date,
                'checkin_time' => $checkin_time,
                'checkout_time' => $checkout_time,
                'active_hours' => $active_hours,
                'status' => $this->statusPresenter::statuses()[$status]
            ]);
        }
    }
}