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

    private $agent;
    private $vendor;
    private $mobile;
    private $amount;
    private $type;

    public function __construct($agent, $vendor, $mobile, $amount, $type)
    {
        $this->agent = $agent;
        $this->vendor = $vendor;
        $this->mobile = $mobile;
        $this->amount = $amount;
        $this->type = $type;
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
        if ($this->attempts() < 1) {
            $vendor = $vendor->getById($this->vendor);
            $top_up->setAgent($this->agent)->setVendor($vendor)->recharge($this->mobile, $this->amount, $this->type);
            if($top_up->isNotSuccessful()) $this->notifyAgentAboutFailure();
            else Redis::rpush('test_done_' . $this->agent->id, $this->mobile);
        }
    }

    /**
     * @throws \Exception
     */
    private function notifyAgentAboutFailure()
    {
        notify($this->agent)->send([
            "title" => 'Your top up to ' . $this->mobile . ' has been failed.',
            "link" => '',
            "type" => notificationType('Danger')
        ]);
    }
}