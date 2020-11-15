<?php namespace Sheba\TopUp;

use Sheba\Reports\ExcelHandler;

class TopUpHistoryExcel
{
    private $excelHandler;
    private $historyData;
    private $data;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }

    public function setData(array $data)
    {
        $this->historyData = $data;
        return $this;
    }

    public function get()
    {
        return $this->excelHandler->setName('topup')->createReport($this->data)->download();
    }
}