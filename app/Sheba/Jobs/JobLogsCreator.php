<?php namespace Sheba\Jobs;

use App\Models\Job;
use App\Models\JobCrmChangeLog;
use App\Models\JobDeclineLog;
use App\Models\JobNoResponseLog;
use App\Models\JobScheduleDueLog;
use App\Models\JobStatusChangeLog;
use App\Models\JobUpdateLog;
use App\Models\Voucher;
use Carbon\Carbon;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Sheba\Dal\JobCancelLog\JobCancelLog;

class JobLogsCreator
{
    use ModificationFields;

    private $job;
    private $requestIdentifier;
    private $now;

    public function __construct(Job $job)
    {
        $this->job = $job;
        $this->requestIdentifier = new RequestIdentification();
        $this->now = Carbon::now()->toDateTimeString();
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function updateLog($log)
    {
        $logData = [
            'job_id' => $this->job->id,
            'log'    => $log
        ];
        $data = $this->withCreateModificationField($this->requestIdentifier->set($logData));
        JobUpdateLog::create($data);
    }

    private function makeLogData($job_id, $old_status, $new_status)
    {
        $data = $this->requestIdentifier->set([
            'job_id' => $job_id,
            'log' => "Job status changed at - $this->now",
            'from_status' => $old_status,
            'to_status' => $new_status
        ]);
        return $this->withCreateModificationField($data);
    }

    public function statusChangeLog($old_status, $new_status)
    {
        JobStatusChangeLog::create($this->makeLogData($this->job->id, $old_status, $new_status));
    }

    public function statusChangeLogMultiple($jobs, $old_status, $new_status)
    {
        $data = [];
        foreach ($jobs as $job) {
            $data[] = $this->makeLogData($job, $old_status, $new_status);
        }
        JobStatusChangeLog::insert($data);
    }

    public function declineLog($decline_reason)
    {
        $data = [
            'job_id' => $this->job->id,
            'partner_id' => $this->job->partnerOrder->partner_id,
            'log' => "Job declined at $this->now",
            'reason' => $decline_reason
        ];
        JobDeclineLog::create($this->withCreateModificationField($data));
        $this->statusChangeLog($this->job->status, JobStatuses::DECLINED);
    }

    public function cancelLog($request_data)
    {
        $data = [
            'job_id' => $this->job->id,
            'from_status' => $this->job->status,
            'log' => "Job cancelled at $this->now",
            'cancel_reason' => $request_data['cancel_reason'],
            'cancel_reason_details' => empty($request_data['cancel_reason_details']) ? null : $request_data['cancel_reason_details'],
        ];
        $data = $this->requestIdentifier->set($data);
        JobCancelLog::create($this->withCreateModificationField($data));
        $this->statusChangeLog($this->job->status, JobStatuses::CANCELLED);
    }

    public function noResponseLog()
    {
        $is_exist = JobNoResponseLog::where([
            ['job_id', '=', $this->job->id],
            ['partner_id', '=', $this->job->partnerOrder->partner_id]
        ])->first();

        if ($is_exist === null) {
            $data = [
                'job_id' => $this->job->id,
                'partner_id' => $this->job->partnerOrder->partner_id
            ];
            JobNoResponseLog::create($this->withCreateModificationField($data));
            $this->statusChangeLog($this->job->status, JobStatuses::NOT_RESPONDED);
        }
    }

    public function scheduleDueLog()
    {
        $is_exist = JobScheduleDueLog::where([
            ['job_id', '=', $this->job->id]
        ])->first();

        if ($is_exist === null) {
            $data = [
                'job_id' => $this->job->id,
                'log' => "Job Schedule Due at $this->now"
            ];
            JobScheduleDueLog::create($this->withCreateModificationField($data));
            $this->statusChangeLog($this->job->status, JobStatuses::SCHEDULE_DUE);
        }
    }

    public function noResponseLogForMultiple($jobs)
    {
        $data = [];
        foreach ($jobs as $job => $partner) {
            $data[] = $this->withCreateModificationField([
                'job_id' => $job,
                'partner_id' => $partner
            ]);
        }
        JobNoResponseLog::insert($data);
        $this->statusChangeLogMultiple(array_keys($jobs), JobStatuses::PENDING, JobStatuses::NOT_RESPONDED);
    }

    public function scheduleDueLogForMultiple($jobs)
    {
        $data = [];
        foreach ($jobs as $job) {
            $data[] = $this->withCreateModificationField([
                'job_id' => $job,
                'log' => "Job Schedule Due at $this->now"
            ]);
        }
        JobScheduleDueLog::insert($data);
        $this->statusChangeLogMultiple($jobs, JobStatuses::ACCEPTED, JobStatuses::SCHEDULE_DUE);
    }

    public function serveDueLogForMultiple($jobs)
    {
        $this->statusChangeLogMultiple($jobs, JobStatuses::PROCESS, JobStatuses::SERVE_DUE);
    }

    public function cmChangeLog($new_crm_id, $old_crm_id)
    {
        $data = [
            'job_id'     => $this->job->id,
            'new_crm_id' => $new_crm_id,
            'old_crm_id' => $old_crm_id
        ];
        JobCrmChangeLog::create($this->withCreateModificationField($data));
    }

    public function cmChangeLogForMultiple(Array $jobs, $new_crm_id)
    {
        $data = [];
        foreach ($jobs as $job_id => $crm_id) {
            $data[] = $this->withCreateModificationField([
                'job_id' => $job_id,
                'new_crm_id' => $new_crm_id,
                'old_crm_id' => $crm_id
            ]);
        }
        JobCrmChangeLog::insert($data);
    }

    public function addPromo(Voucher $voucher)
    {
        $log = json_encode(['msg' => "$voucher->code voucher added at $this->now", 'voucher_id' => $voucher->id]);
        $log_data = ['job_id' => $this->job->id, 'log' => $log];
        $data = $this->withCreateModificationField($this->requestIdentifier->set($log_data));
        JobUpdateLog::create($data);
    }
}
