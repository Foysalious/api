<?php namespace App\Sheba\Business\Payslip;

use Excel as Payslip;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Excel
{
    private $data=[];
    private $payslipData;
    private $name;
    /**
     * @var BusinessMemberRepositoryInterface
     */
    private $businessMemberRepo;

    public function __construct(BusinessMemberRepositoryInterface $business_member_repo)
    {
        $this->businessMemberRepo = $business_member_repo;
    }

    public function setPayslipData(array $payslip_data)
    {
        $this->payslipData = $payslip_data;
        return $this;
    }

    public function setPayslipName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        $file_name = $this->name;
        Payslip::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->data, null, 'A1', true, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:I1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }

    private function makeData()
    {
        foreach ($this->payslipData as $payslip) {
            $business_member = $this->businessMemberRepo->find($payslip['business_member_id']);
            $member = $business_member->member;
            $profile = $member->profile;
            $bank = $profile->banks->last();
            array_push($this->data, [
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
    }

    private function getHeaders()
    {
        return ['Employee ID', 'Employee Name', 'Department', 'Gross Salary', 'Addition', 'Deduction', 'Net Payable', 'Bank Name', 'Bank Account No.'];
    }

}
