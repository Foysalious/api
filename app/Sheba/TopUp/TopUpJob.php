<?php namespace Sheba\TopUp;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUpJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $agent;
    protected $vendor;
    protected $topUpRequest;

    public function __construct($agent, $vendor, TopUpRequest $top_up_request)
    {
        $this->agent = $agent;
        $this->vendor = $vendor;
        $this->topUpRequest = $top_up_request;
    }

    /**
     * Execute the job.
     *
     * @param  TopUp $top_up
     * @param VendorFactory $vendor
     * @return void
     * @throws \Exception
     */
    public function handle(TopUp $top_up, VendorFactory $vendor)
    {
        if ($this->attempts() < 2) {
            $vendor = $vendor->getById($this->vendor);
            $top_up->setAgent($this->agent)->setVendor($vendor)->recharge($this->topUpRequest);
            if($top_up->isNotSuccessful()) {
                $this->takeUnsuccessfulAction($top_up);
            } else {
                $this->takeSuccessfulAction($top_up);
            }
        }
    }

    /**
     * @param TopUp $top_up
     * @throws \Exception
     */
    protected function takeUnsuccessfulAction(TopUp $top_up)
    {
        $this->notifyAgentAboutFailure();
    }

    /**
     * @param TopUp $top_up
     * @throws \Exception
     */
    protected function takeSuccessfulAction(TopUp $top_up)
    {
        //
    }

    /**
     * @throws \Exception
     */
    private function notifyAgentAboutFailure()
    {
        notify($this->agent)->send([
            "title" => 'Your top up to ' . $this->topUpRequest->getMobile() . ' has been failed.',
            "link" => '',
            "type" => notificationType('Danger')
        ]);
    }
}