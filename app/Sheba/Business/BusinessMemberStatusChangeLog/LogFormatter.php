<?php namespace Sheba\Business\BusinessMemberStatusChangeLog;

class LogFormatter
{
    private $employeeStatusChangeLogs;
    private $formattedLogData = [];

    /**
     * @param $logs
     * @return $this
     */
    public function setEmployeeStatusChangeLogs($logs)
    {
        $this->employeeStatusChangeLogs = $logs;
        return $this;
    }

    /**
     * @return array
     */
    public function format()
    {
        foreach ($this->employeeStatusChangeLogs as $key => $change_log) {
            $this->formattedLogData[$key]['dateTime'] = $change_log->created_at->format('h:i A - d M, Y');
            $this->formattedLogData[$key]['log'] = $change_log->log;
        }

        return $this->formattedLogData;
    }
}