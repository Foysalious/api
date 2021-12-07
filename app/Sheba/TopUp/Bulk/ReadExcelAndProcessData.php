<?php namespace Sheba\TopUp\Bulk;

use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel as MaatwebsiteExcel;
use Sheba\FileManagers\CdnFileManager;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpExcel;

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
    private $headingError;

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
        $this->fileExt = $file->getClientOriginalExtension();
        $upload_path = $file->storeAs("top_up_bulk", $this->makeFileName());
        $this->filePath = storage_path('app') . "/" . $upload_path;

        $data = MaatwebsiteExcel::toArray(new \stdClass(), $file)[0];

        /*$data = Excel::selectSheets(TopUpExcel::SHEET)->load($this->filePath, function (LaravelExcelReader $reader) {
            $reader->formatDates(false)->ignoreEmpty();
        })->get();*/

        $this->data = collect($data)->filter(function ($row) {
            if ($row[0] == "mobile") return false;
            return ($row[0] && $row[1] && $row[2] && $row[3]);
        })->map(function ($row) {
            return (object)[
                'mobile' => $row[0],
                'operator' => $row[1],
                'connection_type' => $row[2],
                'amount' => $row[3],
            ];
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
        $file_name = $this->makeFileName();
        return $this->saveFileToCDN($this->filePath, getBulkTopUpFolder(), $file_name);
    }

    private function makeFileName(): string
    {
        return now()->timestamp . "_bulk_topup_excel_". strtolower(class_basename($this->agent)) . "_" . $this->agent->id . "." . $this->fileExt;
    }
}
