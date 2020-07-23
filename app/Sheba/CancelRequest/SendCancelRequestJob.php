<?php namespace Sheba\CancelRequest;


use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCancelRequestJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $jobModel;
    private $cancelReason;
    private $escalatedStatus;
    private $crmRequester;
    private $username;
    private $userAgentInformation;

    public function __construct(\App\Models\Job $job_model, $cancel_reason, $username, $escalated_status, $crm_requester = 1, $userAgentInformation)
    {
        $this->jobModel = $job_model;
        $this->cancelReason = $cancel_reason;
        $this->escalatedStatus = $escalated_status;
        $this->crmRequester = $crm_requester;
        $this->username = $username;
        $this->userAgentInformation = $userAgentInformation;
    }

    public function handle()
    {
        /** @var Requestor $requester */
        $requester = $this->crmRequester ? app(CmRequestor::class) : app(PartnerRequestor::class);
        $requester->setJob($this->jobModel)->setReason($this->cancelReason)->setEscalatedStatus($this->escalatedStatus)
            ->setUserAgentInformation($this->userAgentInformation);
        if ($requester->hasError()) return;
        if ($this->attempts() > 2) return;
        $requester->request();
    }
}