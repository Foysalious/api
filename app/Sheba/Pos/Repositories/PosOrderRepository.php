<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrder;
use Sheba\Repositories\BaseRepository;

class PosOrderRepository extends BaseRepository
{
    /**
     * @param $data
     * @return PosOrder
     */
    public function save($data)
    {
        return PosOrder::create($this->withCreateModificationField($data));
    }
}