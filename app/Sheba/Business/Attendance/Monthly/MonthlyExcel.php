<?php namespace Sheba\Business\Attendance\Monthly;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sheba\Helpers\TimeFrame;

class MonthlyExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    private $monthlyData;
    /** @var TimeFrame */
    private $timeFrame;

    public function __construct(array $monthly_data, TimeFrame $time_frame)
    {
        $this->monthlyData = $monthly_data;
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->monthlyData as $employee) {
            $data->push([
                'employee_id' => $employee['employee_id'],
                'name' => $employee['member']['name'],
                'dept' => $employee['department']['name'],
                'working_days' => $employee['attendance']['working_days'],
                'present' => $employee['attendance']['present'],
                'on_time' => $employee['attendance']['on_time'],
                'late' => $employee['attendance']['late'],
                'left_timely' => $employee['attendance']['left_timely'],
                'left_early' => $employee['attendance']['left_early'],
                'on_leave' => $employee['attendance']['on_leave'],
                'absent' => $employee['attendance']['absent'],
                'total_hours' => $employee['attendance']['total_hours'],
                'overtime' => $employee['attendance']['overtime'],
                'joining_prorated' => $employee['joining_prorated']
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Employee Name', 'Department', 'Working Days', 'Present',
            'On time', 'Late', 'Left Timely', 'Left early', 'On leave', 'Absent',
            'Total Hours', 'Overtime', 'Joining Prorated'
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
        $sheet->getStyle('A:N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    public function title(): string
    {
        return $this->timeFrame->toDateString();
    }
}
