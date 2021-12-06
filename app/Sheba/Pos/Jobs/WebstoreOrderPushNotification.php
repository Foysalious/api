<?php namespace Sheba\Pos\Jobs;


use App\Models\PosOrder;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\WebstorePushNotificationHandler;

class WebstoreOrderPushNotification implements ShouldQueue
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
     * @param WebstorePushNotificationHandler $handler
     * @throws Exception
     */
    public function handle(WebstorePushNotificationHandler $handler)
    {
        if ($this->attempts() > 2) return;
        $handler->setOrder($this->order)->handle();
    }
}