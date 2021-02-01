<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use Exception;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Vendor\VendorFactory;
use Sheba\TopUp\TopUpCompletedEvent;

class TopUpJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const QUEUE_NAME = 'topup:high';

    /** @var TopUp */
    protected $topUp;
    /** @var VendorFactory */
    private $vendorFactory;
    /** @var FailedJobProviderInterface */
    private $failedJobLogger;

    protected $agent;
    protected $vendorId;
    /** @var Vendor */
    protected $vendor;
    /** @var TopUpOrder */
    protected $topUpOrder;

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
     * @param VendorFactory $vendor_factory
     * @param TopUp $top_up
     * @param FailedJobProviderInterface|null $logger
     * @return void
     */
    public function handle(VendorFactory $vendor_factory, TopUp $top_up, FailedJobProviderInterface $logger = null)
    {
        if ($this->attempts() > 1) return;

        $this->vendorFactory = $vendor_factory;
        $this->topUp = $top_up;
        $this->failedJobLogger = $logger;

        try {
            $this->_handle();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * @throws Exception
     */
    private function _handle()
    {
        $this->vendor = $this->vendorFactory->getById($this->vendorId);
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

    private function handleException(Exception $e)
    {
        $id = $this->failedJobLogger->log($this->connection, $this->queue, $this->job->getRawBody());
        logErrorWithExtra($e, [
            config('queue.failed.table') . ".id" => $id
        ]);
    }
}
