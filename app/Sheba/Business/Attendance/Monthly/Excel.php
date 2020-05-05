<?php namespace Sheba\Business\Attendance\Monthly;


use Sheba\Reports\ExcelHandler;

class Excel
{
    private $monthlyData;
    private $excelHandler;
    private $data;

    public function __construct(ExcelHandler $excelHandler)
    {
        $this->excelHandler = $excelHandler;
        $this->data = [];
    }

    public function setMonthlyData(array $monthly_data)
    {
        $this->monthlyData = $monthly_data;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        return $this->excelHandler->setName('Monthly Attendance')->createReport($this->data)->download();
    }

    private function makeData()
    {
        foreach ($this->monthlyData as $employee) {
            array_push($this->data, [
                'name' => $employee['member']['name'],
                'dept' => $employee['department']['name'],
                'present' => $employee['attendance']['present'],
                'on_time' => $employee['attendance']['on_time'],
                'late' => $employee['attendance']['late'],
                'left_early' => $employee['attendance']['left_early'],
                'absent' => $employee['attendance']['absent'],
                'leave' => $employee['attendance']['on_leave']
            ]);
        }
    }
}