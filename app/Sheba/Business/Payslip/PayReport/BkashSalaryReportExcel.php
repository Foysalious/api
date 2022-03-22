<?php namespace Sheba\Business\Payslip\PayReport;

use Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class BkashSalaryReportExcel implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    private $payReportData;

    public function __construct(array $pay_report_data)
    {
        $this->payReportData = $pay_report_data;
    }

    public function setFile($file)
    {
        return ['Employee Id', 'Employee Name', 'Bkash Number', 'Net Payable'];
    }

    public function makeData()
    {
        $this->loadExcel();

        $sl_no = 0;
        foreach ($this->payReportData as $key => $pay_report_data) {
            $this->excel->getActiveSheet()->setCellValue(BkashPayslipExcel::SL_NO . ($key + 4), ++$sl_no);
            $this->excel->getActiveSheet()->setCellValue(BkashPayslipExcel::WALLET_NO . ($key + 4), $pay_report_data['account_no']);
            $this->excel->getActiveSheet()->setCellValue(BkashPayslipExcel::PRINCIPLE_AMOUNT . ($key + 4), $pay_report_data['net_payable']);
        }
        $this->excel->save();

        return $this;
    }

    private function loadExcel()
    {
        $this->excel = Excel::selectSheets([BkashPayslipExcel::FINAL_DISBURSEMENT, BkashPayslipExcel::CLIENT, BkashPayslipExcel::FINAL, BkashPayslipExcel::FEE, BkashPayslipExcel::BKASH])->load($this->file);
    }

    public function takeCompletedAction()
    {
        $this->excel->download('xls');
        unlink($this->file);
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
