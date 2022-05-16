<?php namespace App\Sheba\Business\Payslip\PayReport;

use Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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

    public function takeCompletedAction()
    {
        $this->loadExcel();

        $sl_no = 0;
        foreach ($this->payReportData as $key => $pay_report_data) {
            $this->excel->getActiveSheet()->setCellValue(BkashPayslipExcel::SL_NO . ($key + 4), ++$sl_no);
            $this->excel->getActiveSheet()->setCellValue(BkashPayslipExcel::WALLET_NO . ($key + 4), $pay_report_data['account_no']);
            $this->excel->getActiveSheet()->setCellValue(BkashPayslipExcel::PRINCIPLE_AMOUNT . ($key + 4), $pay_report_data['net_payable']);
        }
        $this->excel->save();
        $file_path = storage_path('exports') . DIRECTORY_SEPARATOR . 'bkash_payable_file.xls';
        $file_name = $this->uniqueFileName($this->excel->title, $this->excel->ext);
        return $this->saveFileToPublicFolder($file_path, '/bKash_excel', $file_name);
    }

    private function loadExcel()
    {
        $this->excel = Excel::selectSheets([BkashPayslipExcel::FINAL_DISBURSEMENT, BkashPayslipExcel::CLIENT, BkashPayslipExcel::FINAL, BkashPayslipExcel::FEE, BkashPayslipExcel::BKASH])->load($this->file);
    }

    private function saveFileToPublicFolder($file_path, $folder, $file_name)
    {
        return $this->putFileToPublicAndGetPath(file_get_contents($file_path), $folder, $file_name);
    }

    private function putFileToPublicAndGetPath($file, $folder, $filename)
    {
        $filename = $this->makeFullFilePath($folder, $filename);
        $public = Storage::disk('exports');
        $public->put($filename, $file);
        return url('/exports'). DIRECTORY_SEPARATOR.$filename;
    }

    private function makeFullFilePath($folder, $filename)
    {
        $filename = clean($filename, '_', ['.', '-']);
        $folder = trim($folder, '/');
        return $folder . '/' . $filename;
    }

    protected function uniqueFileName($name, $ext = null)
    {
        if (empty($name)) {
            $name = "bkash_payable_file";
        }
        $name = strtolower(str_replace(' ', '_', $name));
        return time() . "_" . $name . "." . $ext;
    }
}
