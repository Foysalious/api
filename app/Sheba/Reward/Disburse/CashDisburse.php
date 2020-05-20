<?php namespace Sheba\Reward\Disburse;


use Sheba\ModificationFields;
use Sheba\Reward\Rewardable;

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
        $this->rewardable->rechargeWallet($amount, $this->getTransactionData($amount, $log));
    }

    private function getTransactionData($amount, $log)
    {
        return ['amount' => $amount, 'log' => $log];
    }

}