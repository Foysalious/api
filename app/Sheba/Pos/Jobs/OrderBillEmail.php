<?php namespace Sheba\Pos\Jobs;

use App\Jobs\Job;
use App\Models\PosOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\EmailHandler;

class OrderBillEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var PosOrder
     */
    private $order;

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
     * @param EmailHandler $handler
     */
    public function handle(EmailHandler $handler)
    {
        if ($this->attempts() <= 2) {
            $handler->setOrder($this->order)->handle();
        }
    }
}