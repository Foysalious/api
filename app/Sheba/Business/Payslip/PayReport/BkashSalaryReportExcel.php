<?php namespace App\Sheba\Business\Payslip\PayReport;

use Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class BkashSalaryReportExcel
{
    private $payReportData;
    private $file;
    /** @var LaravelExcelReader */
    private $excel;

    public function setEmployeeData(array $pay_report_data)
    {
        $this->payReportData = $pay_report_data;
        return $this;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
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
}
