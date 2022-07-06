<?php namespace Sheba\Pos\Jobs;

use App\Jobs\Job;
use Sheba\Pos\Notifier\SmsDataGenerator;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Pos\Notifier\SmsHandler;


class OrderBillSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $orderId;
    protected $tries = 1;
    private $partner;

    public function __construct($partner, $orderId)
    {
        $this->partner = $partner;
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     * @param SmsHandler $handler
     * @throws Exception
     */
    public function handle(SmsHandler $handler)
    {
        if ($this->attempts() > 2) return;
        try {
            /** @var SmsDataGenerator $smaData */
            $smaDataGenerator = app(SmsDataGenerator::class);
            $data = $smaDataGenerator->setPartner($this->partner)->setOrderId($this->orderId)->getData();
            $handler->setPartner($this->partner)->setData($data)->handle();
        } catch (Exception $e) {
            app('sentry')->captureException($e);
        }

    }
}
