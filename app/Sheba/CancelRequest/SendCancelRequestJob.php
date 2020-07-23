<?php namespace Sheba\CancelRequest;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCancelRequestJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $sendCancelRequest;

    public function __construct(SendCancelRequest $sendCancelRequest)
    {
        $this->sendCancelRequest = $sendCancelRequest;
    }

    public function handle(CancelRequestFactory $cancelRequestFactory)
    {
        if ($this->attempts() > 2) return;
        $requester = $cancelRequestFactory->setRequestedBy($this->sendCancelRequest->getRequestedByType())->get();
        $requester->setRequest($this->sendCancelRequest);
        if ($requester->hasError()) return;
        $requester->request();
    }
}