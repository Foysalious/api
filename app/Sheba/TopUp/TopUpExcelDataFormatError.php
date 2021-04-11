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

    public function setAgent($agent): TopUpExcelDataFormatError
    {
        $this->agent = $agent;
        return $this;
    }

    public function setFile($file): TopUpExcelDataFormatError
    {
        $this->file = $file;
        return $this;
    }

    public function setRow($row): TopUpExcelDataFormatError
    {
        $this->row = $row;
        return $this;
    }

    private function getExcel(): LaravelExcelReader
    {
        if (!$this->excel) $this->excel = Excel::selectSheets(TopUpExcel::SHEET, 'suggestion')->load($this->file);
        return $this->excel;
    }

    /**
     * @param null $message
     */
    public function updateExcel($message = null)
    {
        if ($message) {
            $this->getExcel()->getActiveSheet()->setCellValue(TopUpExcel::MESSAGE_COLUMN . $this->row, $message);
            $this->excel->save();
        }
    }

    public function takeCompletedAction(): string
    {
        $name = strtolower(class_basename($this->agent)) . '_' . dechex($this->agent->id);
        $file_name = $this->uniqueFileName($this->file, $name, $this->getExcel()->ext);
        $file_path = $this->saveFileToCDN($this->file, getBulkTopUpFolder(), $file_name);
        unlink($this->file);

        return $file_path;
    }
}
