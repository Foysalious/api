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
        return $this->excelHandler->setFilename('data')->setColumnFormat(['users_email', 'title', 'leave_type_id', 'start_date', 'end_date', 'note', 'is_half_day', 'half_day_configuration', 'approver_id', 'message'])->createReport($this->data)->download();
    }

    private function makeData()
    {
        #foreach ($this->adjustmentData as $employee) {
            array_push($this->data, [
                'users_email'            => '',
                'title'                  => '',
                'leave_type_id'          => '',
                'start_date'             => '',
                'end_date'               => '',
                'note'                   => '',
                'is_half_day'            => '',
                'half_day_configuration' => '',
                'approver_id'            => '',
                'message'                => ''
            ]);
        #}
    }
}