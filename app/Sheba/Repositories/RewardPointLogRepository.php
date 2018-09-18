<?php namespace Sheba\Repositories;

use App\Models\RewardPointLog;
use Sheba\ModificationFields;

class RewardPointLogRepository extends BaseRepository
{
    use ModificationFields;

    public function storeInLog()
    {
        
    }

    public function storeOutLog($creator, $amount, $log = null)
    {
        $data = [
            'target_type' => get_class($creator),
            'target_id' => $creator->id,
            'transaction_type' => 'Out',
            'amount' => $amount,
            'log' => $log
        ];

        RewardPointLog::create($this->withCreateModificationField($data));
    }
}