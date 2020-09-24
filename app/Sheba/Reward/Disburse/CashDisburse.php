<?php namespace Sheba\Reward\Disburse;

use Sheba\FraudDetection\TransactionSources;
use Sheba\Reward\Rewardable;
use Sheba\Transactions\Types;
use Sheba\Transactions\Wallet\WalletTransactionHandler;

class CashDisburse
{

    /** @var Rewardable */
    private $rewardable;

    public function setRewardable(Rewardable $rewardable)
    {
        $this->rewardable = $rewardable;
        return $this;
    }

    public function credit($amount, $log, $tags = null)
    {
        $transaction = (new WalletTransactionHandler())->setModel($this->rewardable)->setSource(TransactionSources::BONUS)
            ->setType(Types::credit())->setAmount($amount)->setLog($log);
        $transaction->store();
    }

}
