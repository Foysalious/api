<?php namespace App\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\CancelRequest\CmRequestor;
use Sheba\CancelRequest\PartnerRequestor;
use Sheba\CancelRequest\Requestor;

class SendCancelRequest extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    private $jobModel;
    private $cancelReason;
    private $escalatedStatus;
    private $crmRequester;

    public function __construct(\App\Models\Job $job_model, $cancel_reason, $escalated_status, $crm_requester = 1)
    {
        $this->jobModel = $job_model;
        $this->cancelReason = $cancel_reason;
        $this->escalatedStatus = $escalated_status;
        $this->crmRequester = $crm_requester;
    }

    public function handle()
    {
        /** @var Requestor $requester */
        $requester = $this->crmRequester ? app(CmRequestor::class) : app(PartnerRequestor::class);
        $requester->setJob($this->jobModel)->setReason($this->cancelReason)->setEscalatedStatus($this->escalatedStatus);
        if ($requester->hasError()) return;
        if ($this->attempts() > 2) return;
        $requester->request();
    }
}