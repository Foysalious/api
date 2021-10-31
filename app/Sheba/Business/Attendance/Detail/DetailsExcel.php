<?php namespace Sheba\Business\Attendance\Detail;

use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\AttendanceConstGetter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sheba\Helpers\TimeFrame;

class DetailsExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    private $breakdownData;
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
    /** @var TimeFrame */
    private $timeFrame;
    private $overtime;
    private $attendanceReconciled;

    public function __construct(BusinessMember $business_member, array $detailed_data, TimeFrame $time_frame)
    {
        $this->setBusinessMember($business_member);
        $this->breakdownData = $detailed_data;
        $this->timeFrame = $time_frame;
    }

    private function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        $this->profile = $this->businessMember->member->profile;
        $this->department = $this->businessMember->department();
    }

    public function getFilename(): string
    {
        $name =  $this->businessMember->employee_id ?
            $this->profile->name . '_' . $this->department->name . '_' . $this->businessMember->employee_id :
            $this->profile->name . '_' . $this->department->name;
        return $name . ".xlsx";
    }

    public function headings(): array
    {
        return ['Date', 'Status', 'Check in time', 'Check in status', 'Check in location',
            'Check in address', 'Check out time', 'Check out status',
            'Check out location', 'Check out address', 'Total Hours', 'Overtime', 'Late check in note', 'Left early note'];
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->breakdownData as $attendance) {
            $this->date = null;
            $this->status = null;

            $this->checkInTime = '-';
            $this->checkInStatus = '-';
            $this->checkInLocation = '-';
            $this->checkInAddress = '';

            $this->checkOutTime = '-';
            $this->checkOutStatus = '-';
            $this->checkOutLocation = '-';
            $this->checkOutAddress = '';

            $this->totalHours = '-';
            $this->overtime = '-';
            $this->lateNote = null;
            $this->leftEarlyNote = null;
            $this->attendanceReconciled = '-';
            if (!$attendance['weekend_or_holiday_tag']) {
                if ($attendance['show_attendance'] == 1) {
                    $this->date = $attendance['date'];
                    $this->checkInOutLogics($attendance);
                    $this->status = AttendanceConstGetter::PRESENT;
                }
                if ($attendance['show_attendance'] == 0) {
                    if ($attendance['is_absent'] == 1) {
                        $this->date = $attendance['date'];
                        $this->status = AttendanceConstGetter::ABSENT;
                    }
                }
            }
            if ($attendance['weekend_or_holiday_tag']) {
                $this->date = $attendance['date'];
                if ($attendance['show_attendance'] == 1) {
                    $this->checkInOutLogics($attendance);
                }
                if ($attendance['weekend_or_holiday_tag'] === AttendanceConstGetter::WEEKEND) {
                    $this->status = 'Weekend';
                } else if ($attendance['weekend_or_holiday_tag'] === AttendanceConstGetter::HOLIDAY) {
                    $this->status = 'Holiday';
                } else if ($attendance['weekend_or_holiday_tag'] === AttendanceConstGetter::FULL_DAY) {
                    $this->status = 'On leave: full day';
                } else if ($attendance['weekend_or_holiday_tag'] === AttendanceConstGetter::FIRST_HALF || $attendance['weekend_or_holiday_tag'] === AttendanceConstGetter::SECOND_HALF) {
                    $this->status = "On leave: half day";
                }
            }
            $data->push([
                'date' => $this->date,
                'status' => $this->status,

                'check_in_time' => $this->checkInTime,
                'check_in_status' => $this->checkInStatus,
                'check_in_location' => $this->checkInLocation,
                'check_in_address' => $this->checkInLocation === AttendanceConstGetter::REMOTE && empty($this->checkInAddress) ? AttendanceConstGetter::LOCATION_FETCH_ERROR_MESSAGE : $this->checkInAddress,

                'check_out_time' => $this->checkOutTime,
                'check_out_status' => $this->checkOutStatus,
                'check_out_location' => $this->checkOutLocation,
                'check_out_address' => $this->checkOutLocation === AttendanceConstGetter::REMOTE && empty($this->checkOutAddress) ? AttendanceConstGetter::LOCATION_FETCH_ERROR_MESSAGE :  $this->checkOutAddress,

                'total_hours' => $this->totalHours,
                'overtime' => $this->overtime,
                'late_check_in_note' => $this->lateNote,
                'left_early_note' => $this->leftEarlyNote,
                'attendance_reconciled' => $this->attendanceReconciled
            ]);
        }
        return $data;
    }

    private function checkInOutLogics($attendance)
    {
        $attendance_check_in = $attendance['attendance']['check_in'];
        $attendance_check_out = $attendance['attendance']['check_out'];

        $this->checkInTime = $attendance_check_in['time'];
        if ($attendance_check_in['status'] === AttendanceConstGetter::LATE) {
            $this->checkInStatus = 'Late';
        }
        if ($attendance_check_in['status'] === AttendanceConstGetter::ON_TIME) {
            $this->checkInStatus = 'On time';
        }
        if ($attendance_check_in['is_remote']) {
            $this->checkInLocation = AttendanceConstGetter::REMOTE;
        } else {
            $this->checkInLocation = "Office IP";
        }
        if ($attendance_check_in['address']) {
            $this->checkInAddress = $attendance_check_in['address'];
        }

        if (!is_null($attendance_check_out)) {
            $this->checkOutTime = $attendance_check_out['time'];

            if ($attendance_check_out['status'] === AttendanceConstGetter::LEFT_EARLY) {
                $this->checkOutStatus = 'Left early';
            }

            if ($attendance_check_out['status'] === AttendanceConstGetter::LEFT_TIMELY) {
                $this->checkOutStatus = 'Left timely';
            }

            if ($attendance_check_out['is_remote']) {
                $this->checkOutLocation = AttendanceConstGetter::REMOTE;
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
            $this->overtime = $attendance['attendance']['overtime'];
        }

        $this->lateNote = $attendance['attendance']['late_note'];
        $this->leftEarlyNote = $attendance['attendance']['left_early_note'];
        $this->attendanceReconciled = $attendance['attendance']['is_attendance_reconciled'] ? 'Yes' : 'No';
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
    }

    public function title(): string
    {
        return $this->timeFrame->toDateString();
    }
}