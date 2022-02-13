<?php namespace Sheba\Business\Payslip\PayReport;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BkashSalaryReportExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $payReportData;

    public function __construct(array $pay_report_data)
    {
        $this->payReportData = $pay_report_data;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $formatted_data = collect([]);
        foreach ($this->payReportData as $pay_report_data) {
            $formatted_data->push([
                'employee_id' => $pay_report_data['employee_id'],
                'employee_name' => $pay_report_data['name'],
                'bkash_number' => $pay_report_data['account_no'],
                'net_payable' => $pay_report_data['net_payable'],
            ]);
        }
        return $formatted_data;
    }

    public function headings(): array
    {
        return ['Employee Id', 'Employee Name', 'Bkash Number', 'Net Payable'];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}