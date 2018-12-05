<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUpJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $agent;
    protected $vendorId;
    protected $vendor;

    /** @var TopUpRequest */
    protected $topUpRequest;

    /** @var TopUp */
    protected $topUp;

    public function __construct($agent, $vendor, TopUpRequest $top_up_request)
    {
        $this->agent = $agent;
        $this->topUpRequest = $top_up_request;
        $this->vendorId = $vendor;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        if ($this->attempts() < 2) {
            $vendor_factory = app(VendorFactory::class);
            $this->vendor = $vendor_factory->getById($this->vendorId);

            $this->topUp = app(TopUp::class);
            $this->topUp->setAgent($this->agent)->setVendor($this->vendor);

            $this->topUp->recharge($this->topUpRequest);
            if($this->topUp->isNotSuccessful()) {
                $this->takeUnsuccessfulAction();
            } else {
                $this->takeSuccessfulAction();
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function takeUnsuccessfulAction()
    {
        $this->notifyAgentAboutFailure();
    }

    /**
     * @throws \Exception
     */
    protected function takeSuccessfulAction()
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