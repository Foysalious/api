<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Sheba\Repositories\BaseRepository;

class PosOrderItemRepository extends BaseRepository
{
    /**
     * @param array $data
     * @return PosOrderItem
     */
    public function save(array $data)
    {
        return PosOrderItem::create($this->withCreateModificationField($data));
    }
}