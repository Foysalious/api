<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Vendor\VendorFactory;
use Sheba\TopUp\TopUpCompletedEvent;

class TopUpJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const QUEUE_NAME = 'topup:high';

    protected $agent;
    protected $vendorId;
    /** @var Vendor */
    protected $vendor;

    /** @var TopUpOrder */
    protected $topUpOrder;

    /** @var TopUp */
    protected $topUp;

    public function __construct($agent, $vendor, TopUpOrder $top_up_order)
    {
        $this->agent = $agent;
        $this->topUpOrder = $top_up_order;
        $this->vendorId = $vendor;
        $this->connection = 'topup';
        $this->queue = self::QUEUE_NAME;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        if ($this->attempts() < 2) {
            $vendor_factory = app(VendorFactory::class);
            $this->vendor = $vendor_factory->getById($this->vendorId);
            $this->topUp = app(TopUp::class);
            $this->topUp->setAgent($this->agent)->setVendor($this->vendor);

            $this->topUp->recharge($this->topUpOrder);

            event(new TopUpCompletedEvent([
                'id' => $this->topUpOrder->id,
                'agent_id' => $this->topUpOrder->agent_id,
                'agent_type' => $this->topUpOrder->agent_type,
                'status' => $this->topUpOrder->status,
                'bulk_request_id' => $this->topUpOrder->bulk_request_id,
            ]));

            if ($this->topUp->isNotSuccessful()) {
                $this->takeUnsuccessfulAction();
            } else {
                $this->takeSuccessfulAction();
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function takeUnsuccessfulAction()
    {
        $this->notifyAgentAboutFailure();
    }

    /**
     * @throws Exception
     */
    protected function takeSuccessfulAction()
    {
        //
    }

    /**
     * @throws Exception
     */
    private function notifyAgentAboutFailure()
    {
        notify($this->agent)->send([
            "title" => 'Your top up to ' . $this->topUpOrder->payee_mobile . ' has been failed.',
            "link" => '',
            "type" => notificationType('Danger')
        ]);
    }

    /**
     * @return TopUpRequest
     */
    public function getTopUpRequest()
    {
        return $this->topUpRequest;
    }

    /**
     * @return TopUpVendor
     */
    public function getVendor()
    {
        return $this->topUpOrder->vendor;
    }

    /**
     * @return TopUpAgent
     */
    public function getAgent()
    {
        return $this->topUpOrder->agent;
    }
}
