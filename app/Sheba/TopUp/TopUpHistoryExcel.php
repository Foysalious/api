<?php namespace Sheba\TopUp;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Excel;

class TopUpHistoryExcel
{
    private $file;
    private $row;
    /** @var LaravelExcelReader $excel */
    private $excel = null;

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function setRow($row)
    {
        $this->row = $row;
        return $this;
    }

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(TopUpExcel::SHEET)->load($this->file);
        return $this->excel;
    }

    public function updateMobile($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::MOBILE_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateOperator($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::VENDOR_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateConnectionType($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::TYPE_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateAmount($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::AMOUNT_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateStatus($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::STATUS_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }
    public function updateName($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::NAME_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }
    public function updateCreatedDate($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::CREATED_DATE_COLUMN . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function takeCompletedAction()
    {
        $this->excel->download('xlsx');
        unlink($this->file);
    }
}