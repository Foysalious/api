<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrderLog;
use Sheba\Repositories\BaseRepository;

class PosOrderLogRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return PosOrderLog
     */
    public function save(array $data)
    {
        return PosOrderLog::create($this->withCreateModificationField($data));
    }
}