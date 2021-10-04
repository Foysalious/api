<?php namespace Sheba\Business\Expense;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $expenseData;

    public function __construct(array $expense_data)
    {
        $this->expenseData = $expense_data;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->expenseData as $expense) {
            $data->push([
                'month' => Carbon::now()->month(($expense['month']))->format('F'),
                'employee_id' => $expense['employee_id'] ?: 'N/A',
                'employee_name' => $expense['employee_name'],
                'employee_department' => $expense['employee_department'],
                'transport' => $expense['transport'],
                'food' => $expense['food'],
                'other' => $expense['other'],
                'amount' => (double)$expense['amount']
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return ['Month', 'Employee ID', 'Employee Name', 'Department', 'Transport', 'Food', 'Other', 'Amount'];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}