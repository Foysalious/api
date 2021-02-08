<?php namespace App\Sheba\Business\SalaryLog;

class Formatter
{
    private $salaryLogs;
    private $grossSalaryLogs = [];

    /**
     * @param $salary_logs
     * @return $this
     */
    public function setSalaryLogs($salary_logs)
    {
        $this->salaryLogs = $salary_logs;
        return $this;
    }

    /**
     * @return array
     */
    public function format()
    {
        foreach ($this->salaryLogs as $log)
        {
            $this->grossSalaryLogs[] = $log->log;
        }
        return $this->grossSalaryLogs;
    }

}
