<?php namespace Sheba\JobService;

use App\Models\Job;
use App\Models\JobService;
use App\Models\JobUpdateLog;
use Illuminate\Support\Facades\DB;
use Sheba\Jobs\Discount;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class JobServiceActions
{
    use ModificationFields;

    public function __construct()
    {
        $this->setModifier(request('manager_resource'));
    }

    public function add()
    {
        dd(5);
    }

    public function delete(JobService $job_service)
    {
        DB::beginTransaction();
        try {
            $job = $job_service->job;
            if (!$this->isDeletable($job)) return ['code' => 400, 'msg' => "You can't delete this service"];
            $old_job_service = clone $job_service;

            $job_service->delete();
            $this->jobServiceLog($old_job_service, "$job_service->name Deleted");
            $this->updateJobDiscount($job);
        } catch (\Exception $exception) {
            DB::rollback();
            app('sentry')->captureException($exception);
            return ['code' => 500];
        }
        DB::commit();
        return ['code' => 200];
    }

    private function jobServiceLog(JobService $job_service, $msg)
    {
        $data = $job_service->toArray();
        $data['msg'] = $msg;
        unset($data['job']);

        $this->jobUpdateLog($job_service->job->id, json_encode($data));
    }

    private function jobUpdateLog($job_id, $log)
    {
        $log_data = [
            'job_id' => $job_id,
            'log'    => $log
        ];
        JobUpdateLog::create($this->withCreateModificationField((new RequestIdentification())->set($log_data)));
    }

    private function updateJobDiscount(Job $job)
    {
        $discount = (new Discount())->get($job);
        $job->update($this->withUpdateModificationField(['discount' => $discount]));
    }

    private function isDeletable(Job $job)
    {
        return (!$job->isCancelRequestPending() && !in_array($job->status, ["Served", "Cancelled"]) && $job->jobServices->count() > 1);
    }
}