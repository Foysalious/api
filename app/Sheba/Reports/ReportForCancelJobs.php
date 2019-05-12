<?php namespace Sheba\Reports;

use App\Models\JobCancelReason;

class ReportForCancelJobs
{
    private $logs;
    private $fileName;
    private $cancelReasons;
    private $jobStatuses;
    private $lifetimes;
    private $excel;

    public function __construct(ExcelHandler $excel, $cancelled_jobs_logs)
    {
        $this->logs = $cancelled_jobs_logs;
        $this->fileName = "Cancel-";
        $this->cancelReasons = JobCancelReason::pluck('name','key')->toArray();
        $this->jobStatuses = array_except(constants('JOB_STATUSES'), ['Cancelled']);
        $this->lifetimes = jobCancelLifetimes();
        $this->excel = $excel;
    }

    /**
     * @throws \Exception
     */
    public function raw()
    {
        $filename = $this->fileName . "Raw";
        $logs = $this->logs;
        $this->excel->setName($filename);
        $this->excel->setViewFile('cancel_raw');
        $this->excel->pushData('cancelled_jobs_logs', $logs);
        $this->excel->download();
    }

    /**
     * @throws \Exception
     */
    public function lifetime()
    {
        $filename = $this->fileName . "Lifetime";
        $logs = $this->logs;
        $data = $this->_calculateJob('count', $logs, "ServiceGroup", "Lifetime");
        $this->_basicCancelReport($filename, $data, "ServiceGroup", "Lifetime");
    }

    /**
     * @param $about
     * @param $based_on
     * @throws \Exception
     */
    public function job($about, $based_on)
    {
        $filename = $this->fileName . "Job$about$based_on";
        $logs = $this->logs;
        $data = $this->_calculateJob('count', $logs, $based_on, $about);
        $this->_basicCancelReport($filename, $data, $based_on, $about);
    }

    /**
     * @param $about
     * @param $based_on
     * @throws \Exception
     */
    public function amount($about, $based_on)
    {
        $filename = $this->fileName . "Amount$about$based_on";
        $logs = $this->logs;
        $data = $this->_calculateJob('amount', $logs, $based_on, $about);
        $this->_basicCancelReport($filename, $data, $based_on, $about);
    }

    /**
     * @param $filename
     * @param $data
     * @param $based_on
     * @param $about
     * @throws \Exception
     */
    private function _basicCancelReport($filename, $data, $based_on, $about)
    {
        if($about == "Reason") $columns = $this->cancelReasons;
        else if($about == "Status") $columns = $this->jobStatuses;
        else if($about == "Lifetime") $columns = $this->lifetimes;

        $this->excel->setName($filename);
        $this->excel->setViewFile('cancel_basic');
        $this->excel->pushData('data', $data)->pushData('based_on', $based_on . " Name")->pushData('columns', array_values($columns));
        $this->excel->download();
    }

    private function _calculateJob($calculateWhat, $cancelled_jobs_logs, $based_on, $about)
    {
        $data = [];
        foreach($cancelled_jobs_logs as $key => $cancelled_job_log) {
            //if ($cancelled_job_log->from_status == 'Cancelled') continue;
            list($row, $column) = $this->_getRowAndColumnFor($based_on, $about, $cancelled_job_log);
            $this->_initializeIfEmpty($data[$row], $about);
            $data[$row][$column] += ($calculateWhat == "amount") ? $cancelled_job_log->job->calculate()->grossPrice : 1;
        }
        return $data;
    }

    private function _getRowAndColumnFor($based_on, $about, $cancelled_job_log)
    {
        return [
            $this->_getRowFor($based_on, $cancelled_job_log),
            $this->_getColumnFor($about, $cancelled_job_log),
        ];
    }

    private function _getRowFor($based_on, $cancelled_job_log)
    {
        $job = $cancelled_job_log->job;
        if($based_on == "Department") {
            return $job->partnerOrder->order->department();
        } else if($based_on == "CRM") {
            return ($job->crm_id) ? $job->crm->name : "Unassigned";
        } else if($based_on == "Partner") {
            return $job->partnerOrder->partner->name;
        } else if($based_on == "ServiceGroup") {
            return $job->category->parent->name;
        }
    }

    private function _getColumnFor($about, $cancelled_job_log)
    {
        if($about == "Reason") {
            return $cancelled_job_log->cancel_reason;
        } else if($about == "Status") {
            return $cancelled_job_log->from_status;
        } else if($about == "Lifetime") {
            return jobCancelLifetimes($cancelled_job_log->job->created_at->diffInDays($cancelled_job_log->created_at));
        }
    }

    private function _initializeIfEmpty(&$row, $about)
    {
        if(empty($row)) {
            $row = [];
            $this->_initialize($row, $about);
        }
    }

    private function _initialize(&$row, $about)
    {
        if ($about == "Reason") {
            $this->_initializeEmptyReasons($row);
        } else if ($about == "Status") {
            $this->_initializeEmptyStatuses($row);
        } else if ($about == "Lifetime") {
            $this->_initializeEmptyLifetimes($row);
        }
    }

    private function _initializeEmptyReasons(&$row)
    {
        $this->_initializeRow($row, $this->cancelReasons);
    }

    private function _initializeEmptyStatuses(&$row)
    {
        $this->_initializeRow($row, $this->jobStatuses);
    }

    private function _initializeEmptyLifetimes(&$row)
    {
        $this->_initializeRow($row, $this->lifetimes);
    }

    private function _initializeRow(&$row, $keys)
    {
        foreach($keys as $key) {
            $row[$key] = 0;
        }
        return $row;
    }
}