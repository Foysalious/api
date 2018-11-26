<?php namespace Sheba\Reports;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExcelHandler
{
    private $name;
    private $filename;
    private $viewFilePath = "reports.excels.";
    private $viewFileName;
    private $sheetName;
    private $downloadFormat = "csv";

    private $autoSize = null;
    private $columnFormat = null;

    private $data;

    public function make($name, $view, $data)
    {
        $this->setName($name);
        $this->setViewFile($view);
        $this->setData($data);
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        $this->setFilenameWithDate($name);
    }

    public function setFilenameWithDate($name, $separator = '_')
    {
        $date = Carbon::now()->toDateString();
        $this->setFilename($date . $separator . $name);
    }

    public function setFilename($name)
    {
        $this->filename = $name . "Report";
    }

    public function setViewFile($name)
    {
        $this->setViewFileWithPath($this->viewFilePath . $name);
    }

    public function setViewFileWithPath($name)
    {
        $this->viewFileName = $name;
    }

    public function pushData($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function setData($value)
    {
        if(!is_array($value)) throw new \InvalidArgumentException('Value is not an array.');
        if(!isAssoc($value)) throw new \InvalidArgumentException('Value is not an associative array.');
        $this->data = $value;
    }

    public function setSheetName($name)
    {
        $this->sheetName = $name;
    }

    public function setDownloadFormat($name)
    {
        $this->sheetName = $name;
    }

    public function setAutoSize($value)
    {
        $this->autoSize = $value;
    }

    public function setColumnFormat(array $value)
    {
        $this->columnFormat = $value;
    }

    public function show()
    {
        return view($this->viewFileName, $this->data);
    }

    /**
     * @throws \Exception
     */
    public function create()
    {
        if(empty($this->data)) throw new \Exception('Invalid Data');
        $this->sheetName = $this->sheetName ?: $this->name;

        return Excel::create($this->filename, function ($excel) {
            $excel->setTitle($this->filename);
            $excel->setCreator('Sheba')->setCompany('Sheba');


            $excel->sheet($this->sheetName, function ($sheet) {
                if(!is_null($this->autoSize)) $sheet->setAutoSize($this->autoSize);
                if(!is_null($this->columnFormat)) $sheet->setColumnFormat($this->columnFormat);

                $sheet->loadView($this->viewFileName, $this->data);
            });
        });
    }

    /**
     * @throws \Exception
     */
    public function get()
    {
        return $this->create()->string($this->downloadFormat);
    }

    /**
     * @throws \Exception
     */
    public function download()
    {
        $this->create()->download($this->downloadFormat);
    }
}