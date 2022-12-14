<?php namespace App\Sheba\Business\Payslip\PayReport;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Sheba\FileManagers\CdnFileManager;

class PayReportPdfHandler
{
    use CdnFileManager;

    private $payReportDetails;
    private $timePeriod;
    private $businessMember;
    private $filename;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setPayReportDetails($pay_report_details)
    {
        $this->payReportDetails = $pay_report_details;
        return $this;
    }

    public function setTimePeriod($time_period)
    {
        $this->timePeriod = $time_period;
        return $this;
    }

    public function setFileName($filename)
    {
        $this->filename = $filename;
        return$this;
    }

    /**
     * @throws Exception
     */
    public function generate()
    {
        $file = $this->getTempFolder() . $this->filename;
        $pay_report_detail = $this->payReportDetails;
        App::make('dompdf.wrapper')->loadView('pdfs.payslip.payroll_details', compact('pay_report_detail'))->save($file);
        $s3_payslip_link = $this->saveToCDN($file, $this->filename);
        unlink($file);
        return $s3_payslip_link;
    }

    private function saveToCDN($file, $filename)
    {
        $s3_payslip_path = 'payslips/';
        return $this->saveFileToCDN($file, $s3_payslip_path, $filename);
    }

    private function getTempFolder()
    {
        $temp_folder = public_path() . '/uploads/payslip/';
        if (!is_dir($temp_folder)) {
            mkdir($temp_folder, 0777, true);
        }
        return $temp_folder;
    }
}