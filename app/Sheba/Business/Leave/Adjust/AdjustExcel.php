<?php namespace Sheba\Business\Leave\Adjust;

use Sheba\Reports\ExcelHandler;
use Sheba\Reports\Exceptions\NotAssociativeArray;

class AdjustExcel
{
    private $adjustmentData;
    private $leave_types;
    private $excelHandler;
    private $data;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }


    public function setBalance(array $leave_adjustment)
    {
        $this->adjustmentData = $leave_adjustment;
        return $this;
    }


    public function setLeaveType(array $leave_types)
    {
        $leave_type_array = [];
        foreach ($leave_types as $leave_type) {
            $leave_type_array[$leave_type['title']] = 0;
        }

        $this->leave_types = $leave_type_array;
        return $this;
    }


    public function get()
    {
        $this->makeData();
        return $this->excelHandler->setName('Leave Adjustment')->createReport($this->adjustmentData)->download();
    }

    /*private function makeData()
    {
        foreach ($this->adjustmentData as $adjustment) {
            array_push($this->data, $adjustment);
        }
    }*/
}
