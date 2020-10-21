<?php namespace Sheba\Business\LeaveAdjustment;

use Sheba\Reports\ExcelHandler;

class GenerateAdjustmentExcel
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


    public function setAdjustmentData(array $leave_adjustment)
    {
        $this->adjustmentData = $leave_adjustment;
        return $this;
    }


    public function setLeaveType(array $leave_types)
    {
        $this->leave_types = $leave_types;
        return $this;
    }


    public function get()
    {
        $this->makeData();
        return $this->excelHandler->setFilename('data')->createReport($this->data)->download();
    }

    private function makeData()
    {
        foreach ($this->leave_types as $type) {
            array_push($this->data, [
                /*'users_email'            => null,
                'title'                  => null,
                'leave_type_id'          => null,
                'start_date'             => null,
                'end_date'               => null,
                'note'                   => null,
                'is_half_day'            => null,
                'half_day_configuration' => null,
                'approver_id'            => null,
                'message'                => null,*/
                'leave_type_id_for_use'  => (int)$type['id'],
                'leave_type_title'       => $type['title'],
                'leave_type_total_days'  => (int)$type['total_days']
            ]);
        }
    }
}