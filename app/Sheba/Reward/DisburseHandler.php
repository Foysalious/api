<?php

namespace Sheba\Reward;

use App\Models\Customer;
use App\Models\Partner;
use App\Models\Reward;
use Sheba\CustomerWallet\CustomerTransactionHandler;
use Sheba\PartnerWallet\PartnerTransactionHandler;
use Sheba\Repositories\CustomerRepository;
use Sheba\Repositories\PartnerRepository;
use Sheba\Repositories\RewardLogRepository;

class DisburseHandler
{
    private $rewardRepo;
    private $reward;

    public function __construct(RewardLogRepository $log_repository)
    {
        $this->rewardRepo = $log_repository;
    }

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    /**
     * @param Rewardable $rewardable
     * @throws \Exception
     */
    public function disburse(Rewardable $rewardable)
    {
        if ($this->isRewardCashType()) {
            $amount = $this->reward->amount;
            $log = $amount . " BDT credited for " . $this->reward->name . " reward";

            if ($rewardable instanceof Partner) {
                (new PartnerTransactionHandler($rewardable))->credit($amount, $log);
            } elseif ($rewardable instanceof Customer) {
                (new CustomerTransactionHandler($rewardable))->credit($amount, $log);
            }
        } elseif ($this->isRewardPointType()) {
            if ($rewardable instanceof Partner) {
                (new PartnerRepository())->updateRewardPoint($rewardable, $this->reward->amount);
            } elseif ($rewardable instanceof Customer) {
                (new CustomerRepository())->updateRewardPoint($rewardable, $this->reward->amount);
            }
        }

        $log = $this->reward->name;
        $this->storeRewardLog($rewardable, $log);
    }

    private function storeRewardLog($rewardable, $log)
    {
        $this->rewardRepo->storeLog($this->reward, $rewardable->id, $log);
    }

    private function isRewardCashType()
    {
        return $this->reward->type == constants('REWARD_TYPE')['Cash'];
    }

    private function isRewardPointType()
    {
        return $this->reward->type == constants('REWARD_TYPE')['Point'];
    }
}