<?php namespace Sheba\TopUp\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\Dal\TopUpGateway\Model as TopUpGateway;

class TopUpBalanceUpdateJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    const QUEUE_NAME = 'topup:high';

    /** @var TopUpGateway */
    protected $topUpGateway;
    protected $balance;

    public function __construct($balance, TopUpGateway $topUpGateway)
    {
        $this->balance = $balance;
        $this->topUpGateway = $topUpGateway;
        $this->connection = 'topup';
        $this->queue = self::QUEUE_NAME;
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $this->topUpGateway->update([
                'balance' => $this->balance
            ]);
        }
    }
}