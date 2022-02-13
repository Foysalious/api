<?php namespace Sheba\Business\Payslip;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class PayslipExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepo;
    private $payslipData;

    public function __construct(BusinessMemberRepositoryInterface $business_member_repo, array $payslip_data)
    {
        $this->businessMemberRepo = $business_member_repo;
        $this->payslipData = $payslip_data;
    }

    public function collection(): Collection
    {
        $data = collect([]);
        foreach ($this->payslipData as $payslip) {
            $business_member = $this->businessMemberRepo->find($payslip['business_member_id']);
            $member = $business_member->member;
            $profile = $member->profile;
            $bank = $profile->banks->last();
            $data->push([
                'employee_id'       => $payslip['employee_id'],
                'employee_name'     => $payslip['employee_name'],
                'department'        => $payslip['department'],
                'gross_salary'      => $payslip['gross_salary'],
                'addition'          => $payslip['addition'],
                'deduction'         => $payslip['deduction'],
                'net_payable'       => $payslip['net_payable'],
                'bank_name'         => $bank ? ucwords(str_replace('_', ' ', $bank->bank_name)) : null,
                'bank_account_no'   =>  $bank ? $bank->account_no : null,
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Gross Salary', 'Addition', 'Deduction', 'Net Payable', 'Bank Name', 'Bank Account No.'];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }
}
