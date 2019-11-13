<?php namespace Sheba\Transactions\Wallet\Jobs;


use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class WalletTransactionJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;
    private $extras;
    /** @var WalletTransactionHandler $handler */
    private $handler;

    public function __construct(WalletTransactionHandler $handler)
    {
        $this->handler = $handler;
        $this->extras = [];
    }

    public function handle()
    {
        $this->handler->store($this->extras, true);
    }

    /**
     * @param mixed $extras
     * @return WalletTransactionJob
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
        return $this;
    }
}
