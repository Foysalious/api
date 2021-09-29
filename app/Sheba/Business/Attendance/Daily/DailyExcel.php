<?php namespace Sheba\Business\Attendance\Daily;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $dailyData;
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
    private $overtime;

    public function __construct(array $daily_data, $date)
    {
        $this->dailyData = $daily_data;
        $this->date = $date;
    }

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
        $this->overtime = '-';
        $this->lateNote = '-';
        $this->leftEarlyNote = '-';
    }

    public function collection()
    {
        $data = [];
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
                $this->overtime = $attendance['overtime'];
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

            array_push($data, [
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
                'overtime' => $this->overtime,
                'late_check_in_note' => $this->lateNote,
                'left_early_note' => $this->leftEarlyNote,
            ]);
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Date', 'Employee ID', 'Employee Name', 'Department',
            'Status', 'Check in time', 'Check in status', 'Check in location',
            'Check in address', 'Check out time', 'Check out status',
            'Check out location', 'Check out address', 'Total Hours', 'Overtime',
            'Late check in note', 'Left early note'
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
        $sheet->getStyle('A:Q')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
