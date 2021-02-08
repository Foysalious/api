<?php namespace App\Sheba\Business\SalaryLog;


class Formatter
{
    private $salaryLogs;
    private $grossSalaryBreakdown = [];

    public function setSalaryLogs($salary_logs)
    {
        $this->salaryLogs = $salary_logs;
        return $this;
    }

    public function format()
    {
        foreach ($this->salaryLogs as $log)
        {
            $this->grossSalaryBreakdown['salary_log'][] = $log->log;
        }
        return $this->grossSalaryBreakdown;
    }

}
