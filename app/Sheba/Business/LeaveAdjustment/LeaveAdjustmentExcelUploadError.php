<?php namespace Sheba\Business\LeaveAdjustment;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Excel;

class LeaveAdjustmentExcelUploadError
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

    public function setRow($row)
    {
        $this->row = $row;
        return $this;
    }

    public function setTotalRow($total_row)
    {
        $this->totalRow = $total_row;
        return $this;
    }

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(AdjustmentExcel::SHEET)->load($this->file);
        return $this->excel;
    }

    public function updateExcel($message = null)
    {
        if ($message) {
            $this->getExcel()->getActiveSheet()->setCellValue(AdjustmentExcel::MESSAGE_COLUMN . $this->row, $message);
            $this->excel->save();
        }
    }

    public function takeCompletedAction()
    {
        if ($this->row == $this->totalRow + 1) {
            $name = strtolower(class_basename($this->agent)) . '_' . dechex($this->agent->id);
            $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
            $file_path = $this->saveFileToCDN($this->file, getBulkLeaveAdjustmentFolder(), $file_name);
            unlink($this->file);
            return $file_path;
        }
    }
}