<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use Exception;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Sheba\TopUp\TopUpRechargeManager;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Vendor\VendorFactory;
use Sheba\TopUp\TopUpCompletedEvent;

class TopUpJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var TopUpRechargeManager */
    protected $topUp;
    /** @var FailedJobProviderInterface */
    private $failedJobLogger;

    /** @var TopUpAgent */
    protected $agent;
    /** @var TopUpOrder */
    protected $topUpOrder;

    public function __construct(TopUpOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        $this->agent = $this->topUpOrder->agent;
        $this->connection = $this->getConnectionName();
        $this->queue = $this->connection;
    }

    /**
     * Execute the job.
     *
     * @param TopUpRechargeManager $top_up
     * @param FailedJobProviderInterface|null $logger
     * @return void
     */
    public function handle(TopUpRechargeManager $top_up, FailedJobProviderInterface $logger = null)
    {
        if ($this->attempts() > 1) return;

        $this->topUp = $top_up;
        $this->failedJobLogger = $logger;

        try {
            $this->_handle();
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * @return string
     */
    private function getConnectionName()
    {
        $connections = config('topup_queues.agent_connections');
        $agent_type = strtolower(class_basename($this->agent));
        if (!array_key_exists($agent_type, $connections)) return $connections['default'];

        $agent_connections = $connections[$agent_type];
        if (array_key_exists($this->agent->id, $agent_connections)) return $agent_connections[$this->agent->id];

        if (array_key_exists("chunk", $agent_connections)) {
            $chunks = $agent_connections["chunk"];
            foreach ($chunks as $chunk) {
                if ($this->agent->id >= $chunk['from'] && $this->agent->id <= $chunk['to']) {
                    return $chunk['connection_name'];
                }
            }
        }

        return $agent_connections['default'];
    }

    /**
     * @throws Exception
     */
    private function _handle()
    {
        $this->topUp->setTopUpOrder($this->topUpOrder)->recharge();

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
        $payload = $this->job->getRawBody();
        $id = $this->failedJobLogger->log($this->connection, $this->queue, $payload);
        logErrorWithExtra($e, [
            config('queue.failed.table') . ".id" => $id
        ]);
    }
}
