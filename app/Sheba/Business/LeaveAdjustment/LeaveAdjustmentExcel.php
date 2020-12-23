<?php namespace Sheba\Business\LeaveAdjustment;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Excel;

class LeaveAdjustmentExcel
{
    use FileManager, CdnFileManager;

    private $agent;
    private $file;
    private $row;
    private $totalRow;
    /** @var LaravelExcelReader $excel */
    private $excel = null;

    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }

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

    public function updateSuperAdminId($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::SUPER_ADMIN_ID . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(AdjustmentExcel::SHEET)->load($this->file);
        return $this->excel;
    }

    public function updateSuperAdminName($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::SUPER_ADMIN_NAME . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateLeaveTypeId($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::LEAVE_TYPE_ID . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateLeaveTypeTile($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::LEAVE_TYPE_TITLE . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function updateLeaveTotalDays($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::TOTAL_DAYS . $this->row, $message);
        $this->excel->save();
        return $this;
    }

    public function takeCompletedAction()
    {
        $this->excel->download('xlsx');
        unlink($this->file);
    }
}
