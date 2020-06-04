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
                'name'          => $employee['member']['name'],
                'dept'          => $employee['department']['name'],
                'working_days'  => $employee['attendance']['working_days'],
                'present'       => $employee['attendance']['present'],
                'on_time'       => $employee['attendance']['on_time'],
                'late'          => $employee['attendance']['late'],
                'left_timely'   => $employee['attendance']['left_timely'],
                'left_early'    => $employee['attendance']['left_early'],
                'on_leave'      => $employee['attendance']['on_leave'],
                'absent'        => $employee['attendance']['absent']
            ]);
        }
    }
}
