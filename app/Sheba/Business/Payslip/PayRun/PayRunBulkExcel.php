<?php namespace Sheba\Business\Payslip\PayRun;

use App\Models\Business;
use PHPExcel_Cell;
use Sheba\Dal\BusinessPayslip\BusinessPayslipRepository;
use Sheba\Dal\PayrollComponent\Components;

class PayRunBulkExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $payrollComponents;
    private $payslip;
    private $maxCell;
    private $scheduleDate;
    /*** @var BusinessPayslipRepository */
    private $businessPayslipRepo;
    private $businessPayslip;

    public function __construct($payslip, $payroll_components)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
        $this->businessPayslipRepo = app(BusinessPayslipRepository::class);
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setPayslips($payslip)
    {
        $this->payslip = $payslip;
        return $this;
    }

    public function setScheduleDate($schedule_date)
    {
        $this->scheduleDate = $schedule_date;
        return $this;
    }

    public function setBusinessPayslipId($business_payslip_id)
    {
        $this->businessPayslip = $this->businessPayslipRepo->find($business_payslip_id);
        $this->scheduleDate = $this->businessPayslip->schedule_date;
        return $this;
    }

    public function setPayrollComponent($payroll_components)
    {
        $this->payrollComponents = $payroll_components;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        $x = 1;
        foreach ($this->payslip as $payslip) {
            $business_member_data = [
                    'serial_no' => sprintf("%03d", $x++),
                    'business_member_id' => $payslip['business_member_id'],
                    'employee_name' => $payslip['employee_name'],
                    'employee_id' => $payslip['employee_id'],
                    'department' => $payslip['department'] ? $payslip['department'] : 'N/A',
                    'schedule_date' => $this->scheduleDate,
                    'gross_salary' => $payslip['gross_salary'],
                ] + $this->getComponents($payslip);
            array_push($this->data, $business_member_data);
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
