<?php namespace Sheba\Repositories;

use App\Models\BonusLog;

class BonusLogRepository extends BaseRepository
{
    /**
     * @param $data
     */
    public function store($data)
    {
        BonusLog::create($this->withCreateModificationField($data));
    }
}