<?php namespace Sheba\Business\EmployeeTracking\EmployeeVisit;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeVisitExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $employeesVisitData;

    public function __construct(array $employee_visit_data)
    {
        $this->employeesVisitData = $employee_visit_data;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->employeesVisitData as $visit) {
            $data->push([
                'schedule_date' => $visit['schedule_date'],
                'employee_name' => $visit['profile']['name'],
                'department' => $visit['profile']['department'],
                'title' => $visit['title'],
                'description' => $visit['description'],
                'status' => $visit['status'],
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return [
            'Visit Date', 'Employee Name', 'Department', 'Title', 'Description', 'Status',
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}