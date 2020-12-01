<?php namespace Sheba\TopUp;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Excel;

class TopUpHistoryExcel
{
    private $file;
    private $row;
    /** @var LaravelExcelReader $excel */
    private $excel = null;
    private $activeSheet;

    public function setFile($file)
    {
        $this->file = $file;
        $this->getExcel();
        return $this;
    }

    public function setRow($row)
    {
        $this->row = $row;
        return $this;
    }

    private function getExcel()
    {
        $this->excel = Excel::selectSheets(TopUpExcel::SHEET, 'suggestion')->load($this->file);
        $this->activeSheet = $this->excel->getActiveSheet();
        return $this->excel;
    }

    public function updateMobile($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::MOBILE_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    public function updateOperator($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::VENDOR_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    public function updateConnectionType($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::TYPE_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    public function updateAmount($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::AMOUNT_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    public function updateStatus($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::STATUS_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    public function updateName($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::NAME_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    public function updateCreatedDate($message)
    {
        $this->activeSheet->setCellValue(TopUpExcel::CREATED_DATE_COLUMN . $this->row, $message);
        $this->excel->save('xlsx');
        return $this;
    }

    /**
     * @param $mobile
     * @param $vendor
     * @param $type
     * @param $amount
     * @param $status
     * @param $name
     * @param $created_date
     * @return $this
     */
    public function updateRow($mobile, $vendor, $type, $amount, $status, $name, $created_date)
    {
        $this->activeSheet->setCellValue(TopUpExcel::MOBILE_COLUMN . $this->row, $mobile);
        $this->activeSheet->setCellValue(TopUpExcel::VENDOR_COLUMN . $this->row, $vendor);
        $this->activeSheet->setCellValue(TopUpExcel::TYPE_COLUMN . $this->row, $type);
        $this->activeSheet->setCellValue(TopUpExcel::AMOUNT_COLUMN . $this->row, $amount);
        $this->activeSheet->setCellValue(TopUpExcel::STATUS_COLUMN . $this->row, $status);
        $this->activeSheet->setCellValue(TopUpExcel::NAME_COLUMN . $this->row, $name);
        $this->activeSheet->setCellValue(TopUpExcel::CREATED_DATE_COLUMN . $this->row, $created_date);

        $this->excel->save('xlsx');
        return $this;
    }

    public function takeCompletedAction()
    {
        unlink($this->file);
        $this->excel->download('xlsx');
    }
}
