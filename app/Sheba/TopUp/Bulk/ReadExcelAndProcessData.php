<?php namespace Sheba\TopUp\Bulk;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Sheba\TopUp\TopUpExcel;
use Excel;

class ReadExcelAndProcessData
{
    private $total;
    private $data;
    /** @var string $filePath */
    private $filePath;

    /**
     * @param UploadedFile $file
     * @return ReadExcelAndProcessData
     */
    public function setExcel(UploadedFile $file): ReadExcelAndProcessData
    {
        $file = Excel::selectSheets(TopUpExcel::SHEET)->load($file)->save();
        $this->filePath = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
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
}
