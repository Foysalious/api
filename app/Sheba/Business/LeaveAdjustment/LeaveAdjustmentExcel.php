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
    /** @var LaravelExcelReader */
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

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(AdjustmentExcel::SHEET)->load($this->file);
        return $this->excel;
    }

    public function setRow($row)
    {
        $this->row = $row;
        return $this;
    }

    public function updateUser($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::USERS_MAIL_COLUMN . $this->row, $message);
        $this->excel->save();
        return;
    }

    public function updateTile($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::TITLE_COLUMN . $this->row, $message);
        $this->excel->save();
        return;
    }

    public function updateMsg($message)
    {
        $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::MESSAGE_COLUMN . $this->row, $message);
        $this->excel->save();
        return;
    }

    public function takeCompletedAction()
    {
        $name = strtolower(class_basename($this->agent)) . '_' . dechex($this->agent->id);
        $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
        $file_path = $this->saveFileToCDN($this->file, getLeaveAdjustmentFolder(), $file_name);
        unlink($this->file);
        return $file_path;

    }
}