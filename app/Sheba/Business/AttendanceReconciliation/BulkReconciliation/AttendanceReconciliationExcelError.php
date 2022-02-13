<?php namespace App\Sheba\Business\AttendanceReconciliation\BulkReconciliation;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Excel;

class AttendanceReconciliationExcelError
{
    use FileManager, CdnFileManager;

    private $file;
    private $row;
    private $totalRow;
    /** @var LaravelExcelReader */
    private $excel = null;
    private $business;

    public function setBusiness($business)
    {
        $this->business = $business;
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

    public function updateExcel($message = null)
    {
        if ($message) {
            $this->getExcel()->getActiveSheet()->setCellValue(AttendanceReconciliationExcel::MESSAGE_COLUMN . $this->row, $message);
            $this->excel->save();
        }
    }

    public function takeCompletedAction()
    {
        $name = strtolower(class_basename($this->business)) . '_' . dechex($this->business->id);
        $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
        $file_path = $this->saveFileToCDN($this->file, getAttendanceReconciliationFolder(), $file_name);
        unlink($this->file);

        return $file_path;
    }

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(AttendanceReconciliationExcel::SHEET)->load($this->file);
        return $this->excel;
    }

}