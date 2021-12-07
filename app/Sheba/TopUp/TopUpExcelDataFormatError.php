<?php namespace App\Sheba\TopUp;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as Reader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Writer;
use Sheba\FileManagers\CdnFileManager;
use Sheba\FileManagers\FileManager;
use Sheba\TopUp\TopUpExcel;

class TopUpExcelDataFormatError
{
    use FileManager, CdnFileManager;

    private $agent;
    private $file;
    private $row;

    /** @var Spreadsheet */
    private $spreadsheet;
    /** @var Worksheet */
    private $worksheet;

    public function setAgent($agent): TopUpExcelDataFormatError
    {
        $this->agent = $agent;
        return $this;
    }

    public function setFile($file): TopUpExcelDataFormatError
    {
        $this->file = $file;

        $this->spreadsheet = (new Reader())->load($file);
        $this->worksheet = $this->spreadsheet->getActiveSheet();

        return $this;
    }

    public function setRow($row): TopUpExcelDataFormatError
    {
        $this->row = $row;
        return $this;
    }

    /**
     * @param null $message
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function updateExcel($message = null)
    {
        if (empty($message)) return;

        $this->worksheet->setCellValue(TopUpExcel::MESSAGE_COLUMN . $this->row, $message);

        (new Writer($this->spreadsheet))->save($this->file);
    }

    public function takeCompletedAction(): string
    {
        $name = strtolower(class_basename($this->agent)) . '_' . dechex($this->agent->id);
        $file_name = $this->uniqueFileName($this->file, $name, getExtensionFromPath($this->file));
        return $this->saveFileToCDN($this->file, getBulkTopUpFolder(), $file_name);
    }
}
