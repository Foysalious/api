<?php namespace Sheba\Business\Payslip\PayRun;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sheba\Dal\PayrollComponent\Components;

class PayRunBulkExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $payrollComponents;
    private $payslip;

    public function __construct($payslip, $payroll_components)
    {
        $this->payslip = $payslip;
        $this->payrollComponents = $payroll_components;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        $x = 1;
        foreach ($this->payslip as $payslip) {
            $data->push([
                'serial_no' => sprintf("%03d", $x++),
                'business_member_id' => $payslip['business_member_id'],
                'employee_name' => $payslip['employee_name'],
                'employee_id' => $payslip['employee_id'],
                'department' => $payslip['department'] ?: 'N/A',
                'schedule_date' => $payslip['schedule_date'],
                'gross_salary' => $payslip['gross_salary'],
            ] + $this->getComponents($payslip));
        }

        return $data;
    }

    public function headings(): array
    {
        $header = ['Serial', 'Business Member ID', 'Employee Name', 'Employee ID', 'Department', 'Schedule Date', 'Gross Salary'];
        $maxCell = 7;
        foreach ($this->payrollComponents as $component) {
            if ($component->is_default) $header_title = Components::getComponents($component->name)['value'];
            $header_title = ucwords(implode(" ", explode("_",$component->name)));

            $header[] = $header_title.':'.$component->type;
            $maxCell++;
        }
        return $header;
    }

    private function getComponents($payslip)
    {
        $additional_business_components = $this->payrollComponents->where('type', 'addition')->pluck('name')->toArray();
        $deductional_business_components = $this->payrollComponents->where('type', 'deduction')->pluck('name')->toArray();
        $data = [];
        foreach ($additional_business_components as $components) {
            foreach ($payslip['addition_breakdown'] as $addition_breakdown) {
                if (!in_array($addition_breakdown['key'], $additional_business_components)) $data[$components] = 0;
                else $data[$addition_breakdown['key']] = $addition_breakdown['value'];
            }
        }
        foreach ($deductional_business_components as $components) {
            foreach ($payslip['deduction_breakdown'] as $deduction_breakdown) {
                if (!in_array($deduction_breakdown['key'], $deductional_business_components)) $data[$components] = 0;
                else $data[$deduction_breakdown['key']] = $deduction_breakdown['value'];
            }
        }
        return $data;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
