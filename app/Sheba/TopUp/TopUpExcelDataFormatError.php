<?php namespace App\Sheba\TopUp;

use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\TopUp\TopUpExcel;
use Excel;

class TopUpExcelDataFormatError
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

    private function getExcel()
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(TopUpExcel::SHEET)->load($this->file);
        return $this->excel;
    }

    public function updateExcel($message = null)
    {
        if ($message) {
            $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::STATUS_COLUMN . $this->row, $message);
            $this->excel->save();
        }
    }

    public function takeCompletedAction()
    {
        $name = strtolower(class_basename($this->agent)) . '_' . dechex($this->agent->id);
        $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
        $file_path = $this->saveFileToCDN($this->file, getBulkTopUpFolder(), $file_name);
        unlink($this->file);

        return $file_path;
    }
}
