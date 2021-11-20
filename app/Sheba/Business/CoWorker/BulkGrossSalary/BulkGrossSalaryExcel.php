<?php namespace Sheba\Business\CoWorker\BulkGrossSalary;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BulkGrossSalaryExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $employeeData;

    public function __construct(array $employee_data)
    {
        $this->employeeData = $employee_data;
    }

    public function collection(): Collection
    {
        $formatted_data = collect([]);
        foreach ($this->employeeData as $employee_data) {
            $formatted_data->push([
                'employee_name' => $employee_data['profile']['name'],
                'employee_email' => $employee_data['profile']['email'],
                'gross_salary' => $employee_data['gross_salary']
            ]);
        }
        return $formatted_data;
    }

    public function headings(): array
    {
        return ['Employee Name', 'Employee Email', 'Gross Salary'];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
