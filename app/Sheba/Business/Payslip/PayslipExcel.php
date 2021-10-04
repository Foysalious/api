<?php namespace App\Sheba\Business\Payslip;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayslipExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $payslipData;

    public function __construct(array $payslip_data)
    {
        $this->payslipData = $payslip_data;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->payslipData as $payslip) {
            $data->push([
                'employee_id'   => $payslip['employee_id'],
                'employee_name' => $payslip['employee_name'],
                'department'    => $payslip['department'],
                'gross_salary'  => $payslip['gross_salary'],
                'addition'      => $payslip['addition'],
                'deduction'     => $payslip['deduction'],
                'net_payable'   => $payslip['net_payable'],
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Gross Salary', 'Addition', 'Deduction', 'Net Payable'];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
