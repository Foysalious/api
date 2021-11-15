<?php namespace Sheba\Pos\Jobs;

use App\Jobs\Job;
use App\Models\PosOrder;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\SmsHandler;

class OrderBillSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var PosOrder
     */
    private $order;
    protected $tries = 1;

    /**
     * Create a new job instance.
     * @param PosOrder $order
     */
    public function __construct(PosOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     * @param SmsHandler $handler
     * @throws Exception
     */
    public function handle(SmsHandler $handler)
    {
        if ($this->attempts() > $this->tries) return;
        $handler->setOrder($this->order)->handle();
    }
}
