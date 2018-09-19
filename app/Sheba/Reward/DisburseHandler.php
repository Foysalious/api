<?php namespace Sheba\Reward;

use App\Models\Reward;
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

    public function disburse(Rewardable $rewardable)
    {
        if ($this->isRewardCashType()) {
            $rewardable->update(['wallet' => floatval($rewardable->wallet) + floatval($this->reward->amount)]);
        } elseif ($this->isRewardPointType()) {
            $rewardable->update(['reward_point' => floatval($rewardable->reward_point) + floatval($this->reward->amount)]);
        }

        $log = "Rewarded " . $this->reward->amount . " ". $this->reward->type;
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