<?php namespace App\Sheba\Business\CoWorker\BulkBkashNumber;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Excel;

class BulkBkashNumberExcelUploadError
{
    use FileManager, CdnFileManager;

    private $file;
    private $row;
    private $totalRow;
    /** @var LaravelExcelReader */
    private $excel = null;

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
            $this->getExcel()->getActiveSheet()->setCellValue(BkashNumberExcel::MESSAGE_COLUMN . $this->row, $message);
            $this->excel->save();
        }
    }

    public function takeCompletedAction()
    {
        $name = strtolower(class_basename($this->business)) . '_' . dechex($this->business->id);
        $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
        $file_path = $this->saveFileToCDN($this->file, getBulkGrossSalaryFolder(), $file_name);
        unlink($this->file);

        return $file_path;
    }

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(BkashNumberExcel::SHEET)->load($this->file);
        return $this->excel;
    }
}