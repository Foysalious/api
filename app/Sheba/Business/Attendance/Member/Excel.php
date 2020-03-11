<?php namespace Sheba\Business\Attendance\Member;

use Sheba\Reports\ExcelHandler;

class Excel
{
    private $monthlyData;
    private $designation;
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
        dd($this->monthlyData);
        return $this;
    }

    public function setDesignation($designation)
    {
        $this->designation = $designation;
        return $this;
    }

    public function get()
    {
        $this->makeData();
        return $this->excelHandler->setName('Employee Monthly Attendance')->createReport($this->data)->download();
    }

    private function makeData()
    {
        foreach ($this->monthlyData as $employee) {
            array_push($this->data, [
                'member_id' => $employee['member']['id'],
                'member_name' => $employee['member']['name'],
                'dept_id' => $employee['department']['id'],
                'dept_name' => $employee['department']['name'],
                'designation' => $this->designation,
                'date' => $employee['date'],
                'checkin_time' => $employee['checkin_time'],
                'checkout_time' => $employee['checkout_time'],
                'status' => $employee['status']
            ]);
        }
    }
}