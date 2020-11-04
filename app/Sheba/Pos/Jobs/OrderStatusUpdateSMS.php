<?php namespace Sheba\Pos\Jobs;


use App\Jobs\Job;
use App\Models\PosOrder;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\StatusUpdateSmsHandler;

class OrderStatusUpdateSMS extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var PosOrder
     */
    private $order;
    protected $tries = 1;
    protected $status;

    /**
     * Create a new job instance.
     * @param PosOrder $order
     * @param $status
     */
    public function __construct(PosOrder $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    /**
     * Execute the job.
     * @param StatusUpdateSmsHandler $handler
     * @throws Exception
     */
    public function handle(StatusUpdateSmsHandler $handler)
    {
        if ($this->attempts() > 2) return;
        $handler->setOrder($this->order)->setStatus($this->status)->handle();
    }


}