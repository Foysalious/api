<?php namespace Sheba\TopUp\Bulk;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\FileManagers\CdnFileManager;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpExcel;
use Excel;

class ReadExcelAndProcessData
{
    use CdnFileManager;

    private $total;
    private $data;
    /** @var string $filePath */
    private $filePath;
    /** @var TopUpAgent $agent */
    private $agent;
    private $fileExt;

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent): ReadExcelAndProcessData
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param UploadedFile $file
     * @return ReadExcelAndProcessData
     */
    public function setExcel(UploadedFile $file): ReadExcelAndProcessData
    {
        $file = Excel::selectSheets(TopUpExcel::SHEET)->load($file)->save();
        $this->fileExt = $file->ext;
        $this->filePath = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $this->fileExt;
        $data = Excel::selectSheets(TopUpExcel::SHEET)->load($this->filePath, function (LaravelExcelReader $reader) {
            $reader->formatDates(false)->ignoreEmpty();
        })->get();

        $this->data = $data->filter(function ($row) {
            return ($row->mobile && $row->operator && $row->connection_type && $row->amount);
        });

        $this->total = $this->data->count();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function saveTopupFileToCDN(): string
    {
        $file_name = Carbon::now()->timestamp . "_bulk_topup_excel_". strtolower(class_basename($this->agent)) . "_" . $this->agent->id . "." . $this->fileExt;
        return $this->saveFileToCDN($this->filePath, getBulkTopUpFolder(), $file_name);
    }
}
