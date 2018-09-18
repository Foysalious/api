<?php namespace Sheba\Repositories;

use App\Models\Reward;
use App\Models\RewardLog;

class RewardLogRepository extends BaseRepository
{
    public function rewardInLog(Reward $reward, $target_id, $log = null)
    {
        $data = [
            'reward_id'     => $reward->id,
            'target_type'   => $reward->target_type,
            'target_id'     => $target_id,
            'log'           => $log
        ];
        RewardLog::create($this->withCreateModificationField($data));
    }

    public function rewardOutLog($target_type, $target_id, $log = null)
    {
        $data = [
            'target_type'       => $target_type,
            'target_id'         => $target_id,
            'log'               => $log,
            'transaction_type'  => 'Out'
        ];
        RewardLog::create($this->withCreateModificationField($data));
    }
}