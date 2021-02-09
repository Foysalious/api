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
        $x = 0;
        foreach ($this->salaryLogs as $log)
        {
            $this->grossSalaryLogs[$x]['dateTime'] = $log->created_at->format('h:i A - d M, Y');
            $this->grossSalaryLogs[$x]['log'] = $log->log;
            $x++;
        }
        return $this->grossSalaryLogs;
    }

}
