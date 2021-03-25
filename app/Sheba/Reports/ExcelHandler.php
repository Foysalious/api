<?php namespace Sheba\Reports;

use Exception;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Sheba\Reports\Exceptions\NotAssociativeArray;

class ExcelHandler extends Handler
{
    /** @var Excel */
    private $excel;

    private $viewFilePath = "reports.excels.";
    private $sheetName;
    private $downloadFormat = "csv";
    private $autoSize = null;
    private $columnFormat = null;

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    /**
     * Creates a generic report. The generic view file is auto loaded.
     * No need to push data externally, all are loaded and downloadable.
     *
     * @param array $value 2D array, on which the data will be loaded.
     * @return $this
     * @throws NotAssociativeArray
     */
    public function createReport($value)
    {
        /** @var Report $report */
        $report = app(Report::class);
        $report = $report->setTitle($this->name)->set($value);
        $this->setViewFile(Report::VIEW_FILE);
        $this->pushData(Report::VARIABLE_NAME, $report);
        return $this;
    }

    /**
     * Returns the report object for the generic report.
     *
     * @return Report
     */
    public function getReport()
    {
        return array_key_exists(Report::VARIABLE_NAME, $this->data) ? $this->data[Report::VARIABLE_NAME] : app(Report::class);
    }

    /**
     * Set sheet name.
     *
     * @param $name
     * @return $this
     */
    public function setSheetName($name)
    {
        $this->sheetName = $name;
        return $this;
    }

    /**
     * Set download format.
     *
     * @param string $name
     * @return $this
     */
    public function setDownloadFormat($name)
    {
        $this->downloadFormat = $name;
        return $this;
    }

    /**
     * Set auto sizing option. (Wrapper on Maatwebsite\Excel)
     *
     * @param bool $value
     * @return $this
     */
    public function setAutoSize($value)
    {
        $this->autoSize = $value;
        return $this;
    }

    /**
     * Set column formatting options. (Wrapper on Maatwebsite\Excel)
     *
     * @param array $value Options to be set on different columns.
     * @return $this
     */
    public function setColumnFormat(array $value)
    {
        $this->columnFormat = $value;
        return $this;
    }

    /**
     * Generate the excel.
     *
     * @return LaravelExcelWriter
     * @throws Exception
     */
    public function create()
    {
        if (empty($this->data)) throw new Exception('Invalid Data');
        $this->sheetName = $this->sheetName ?: $this->name;

        return $this->excel->create($this->filename, function (LaravelExcelWriter $excel) {
            $excel->setTitle($this->filename);
            $excel->setCreator('Sheba')->setCompany('Sheba');

            $excel->sheet($this->sheetName, function (LaravelExcelWorksheet $sheet) {
                if (!is_null($this->autoSize)) $sheet->setAutoSize($this->autoSize);
                if (!is_null($this->columnFormat)) $sheet->setColumnFormat($this->columnFormat);
                $sheet->loadView($this->viewFileName, $this->data);
            });
        });
    }

    /**
     * Get the excel.
     *
     * @throws Exception
     */
    public function get()
    {
        return $this->create()->string($this->downloadFormat);
    }

    /**
     * Generate and download the excel.
     *
     * @param bool $mPdf
     * @throws Exception
     */
    public function download($mPdf=false)
    {
        $this->create()->download($this->downloadFormat);
    }

    /**
     * Generate and save the excel.
     *
     * @return string Path of the saved file.
     * @throws Exception
     */
    public function save()
    {
        $file = $this->create()->save($this->downloadFormat);
        return $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
    }

    protected function getViewPath()
    {
        return $this->viewFilePath;
    }
}
