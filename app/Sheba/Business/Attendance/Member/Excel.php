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
            $checkin_status = null;
            $checkin_address = null;
            $checkin_is_remote = null;

            $checkout_time = null;
            $checkout_status = null;
            $checkout_address = null;
            $checkout_is_remote = null;

            $active_hours = null;

            if (!$attendance['weekend_or_holiday_tag']) {
                if ($attendance['show_attendance'] == 1) {
                    $date = $attendance['date'];

                    $checkin_time = $attendance['attendance']['check_in']['time'];
                    $checkin_status = $attendance['attendance']['check_in']['status'];
                    $checkin_address = 'hihih';
                    #$checkin_address = $attendance['attendance']['check_in']['address'];
                    $checkin_is_remote = $attendance['attendance']['check_in']['is_remote'];

                    $checkout_time = $attendance['attendance']['check_out']['time'];
                    $checkout_status = $attendance['attendance']['check_out']['status'];
                    #$checkout_address = $attendance['attendance']['check_out']['address'];
                    $checkout_address = "hihi";
                    $checkout_is_remote = $attendance['attendance']['check_out']['is_remote'];

                    $active_hours = $attendance['attendance']['active_hours'];
                }
                if ($attendance['show_attendance'] == 0) {
                    if ($attendance['is_absent'] == 1) {
                        $date = $attendance['date'];
                        $checkin_status = 'absent';
                    }
                    if ($attendance['is_absent'] == 0) {
                        $date = $attendance['date'];
                    }
                }
            }
            if ($attendance['weekend_or_holiday_tag']) {
                if ($attendance['show_attendance'] == 0) {
                    $date = $attendance['date'];
                    $checkin_status = $attendance['weekend_or_holiday_tag'];
                }
                if ($attendance['show_attendance'] == 1) {
                    $date = $attendance['date'];

                    $checkin_time = $attendance['attendance']['check_in']['time'];
                    $checkin_status = $attendance['attendance']['check_in']['status'];
                    #$checkin_address = $attendance['attendance']['check_in']['address'];
                    $checkin_address = 'hello';
                    $checkin_is_remote = $attendance['attendance']['check_in']['is_remote'];

                    $checkout_time = $attendance['attendance']['check_out']['time'];
                    $checkout_status = $attendance['attendance']['check_out']['status'];
                    #$checkout_address = $attendance['attendance']['check_out']['address'];
                    $checkout_address = 'hi';
                    $checkout_is_remote = $attendance['attendance']['check_out']['is_remote'];

                    $active_hours = $attendance['attendance']['active_hours'];
                }
            }
            array_push($this->data, [
                'member_id' => $this->member->id,
                'member_name' => $this->member->profile->name,
                'dept_name' => $this->department,
                'designation' => $this->designation,
                'date' => $date,

                'checkin_time' => $checkin_time,
                'checkin_status' => $checkin_status,
                'checkin_address' => $checkin_address,
                'checkin_is_remote' => $checkin_is_remote,

                'checkout_time' => $checkout_time,
                'checkout_status' => $checkout_status,
                'checkout_address' => $checkout_address,
                'checkout_is_remote' => $checkout_is_remote,

                'active_hours' => $active_hours,
            ]);
        }
    }
}