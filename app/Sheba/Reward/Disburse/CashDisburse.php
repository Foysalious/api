<?php namespace Sheba\Reward\Disburse;

use Sheba\FraudDetection\TransactionSources;
use Sheba\Reward\Rewardable;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class CashDisburse
{

    /** @var Rewardable */
    private $rewardable;
    private $transaction;

    public function setRewardable(Rewardable $rewardable)
    {
        $this->rewardable = $rewardable;
        return $this;
    }

    public function credit($amount, $log, $tags = null)
    {
        $transaction = (new WalletTransactionHandler())->setModel($this->rewardable)->setSource(TransactionSources::BONUS)
            ->setType(Types::credit())->setAmount($amount)->setLog($log);
        $this->transaction = $transaction->store();
    }

    public function getTransaction(){
        return $this->transaction;
    }
}
