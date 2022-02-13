<?php namespace Sheba\Business\Payslip\TaxHistory;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaxHistoryExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $taxHistoryData;

    public function __construct(array $tax_history_data)
    {
        $this->taxHistoryData = $tax_history_data;
    }

    public function collection(): Collection
    {
        $data = collect([]);

        foreach ($this->taxHistoryData as $tax_history) {
            $data->push([
                'employee_id' => $tax_history['employee_id'],
                'employee_name' => $tax_history['employee_name'],
                'department' => $tax_history['department'],
                'basic_salary' => $tax_history['basic_salary'],
                'house_rent' => $tax_history['house_rent'],
                'conveyance' => $tax_history['conveyance'],
                'medical_allowance' => $tax_history['medical_allowance'],
                'others' => $tax_history['others'],
                'total_taxable_income' => $tax_history['total_taxable_income'],
                'exemption_amount' => $tax_history['exemption_amount'],
                'remaining_taxable_income' => $tax_history['remaining_taxable_income'],
                '5_percent_slab' => $tax_history['5_percent_slab'],
                '10_percent_slab' => $tax_history['10_percent_slab'],
                '15_percent_slab' => $tax_history['15_percent_slab'],
                '20_percent_slab' => $tax_history['20_percent_slab'],
                '25_percent_slab' => $tax_history['25_percent_slab'],
                'total_tax_amount_yearly' => $tax_history['total_tax_amount_yearly'],
                'total_tax_amount_monthly' => $tax_history['total_tax_amount_monthly'],
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Employee ID', 'Employee Name', 'Department', 'Basic', 'House Rent', 'Conveyance', 'Medical', 'Others',
            'Total Taxable Income', 'Exemption Amount', 'Remaining Taxable Income',
            '5 % slab', '10 % slab', '15 % slab', '20 % slab', '25 % slab',
            'Total Tax Amount(Yearly)', 'Total Tax Amount(Monthly)'
        ];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->freezePane('D2');
        $sheet->getStyle('A1:R1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}