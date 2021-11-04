<?php namespace App\Sheba\Business\Payslip\PayReport;

use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Carbon\Carbon;
use Excel;

class BkashSalaryReportExcel
{
    use FileManager, CdnFileManager;

    private $payReportData;

    public function setEmployeeData(array $pay_report_data)
    {
        $this->payReportData = $pay_report_data;
        return $this;
    }

    public function download()
    {
        $six_digit_random_number = random_int(100000, 999999);
        $file_name = 'Pay_Report_Bkash_Report_' . $six_digit_random_number . '_' . Carbon::now()->toDateTimeString();
        $file = Excel::create($file_name, function ($excel) {
            $excel->sheet('data', function ($sheet) {
                $sheet->fromArray($this->makeData(), null, 'A1', false, false);
                $sheet->prependRow($this->getHeaders());
                $sheet->freezeFirstRow();
                $sheet->cell('A1:D1', function ($cells) {
                    $cells->setFontWeight('bold');
                });
                $sheet->getDefaultStyle()->getAlignment()->applyFromArray(
                    array('horizontal' => 'left')
                );
                $sheet->setAutoSize(true);
            });
        })->save();

        $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
        $file_name = $this->uniqueFileName($file_path, $file_name, 'xlsx');
        $file_link = $this->saveFileToCDN($file_path, getBulkGrossSalaryFolder(), $file_name);
        unlink($file_path);

        return $file_link;
    }

    private function makeData()
    {
        $formatted_data = [];
        foreach ($this->payReportData as $pay_report_data) {
            $formatted_data [] = [
                'employee_id' => $pay_report_data['employee_id'],
                'employee_name' => $pay_report_data['name'],
                'bkash_number' => $pay_report_data['account_no'],
                'net_payable' => $pay_report_data['net_payable'],
            ];
        }
        return $formatted_data;
    }

    private function getHeaders()
    {
        return ['Employee Id', 'Employee Name', 'Bkash Number', 'Net Payable'];
    }
}