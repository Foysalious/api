<?php namespace Sheba\Transactions\Wallet\Jobs;


use App\Jobs\Job;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\FraudDetection\Repository\TransactionRepository;
use Sheba\Transactions\Wallet\WalletTransaction;

class FraudTransactionJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /** @var TransactionRepository */
    private $repo;
    private $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function handle()
    {
        try {
            $this->repo=new TransactionRepository();
            $this->repo->store($this->data);
        } catch (Exception $e) {
            WalletTransaction::throwException($e);
        }
    }

    /**
     * @param mixed $data
     * @return FraudTransactionJob
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}
