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

    /**
     * @param PosOrder $order
     * @param array $data
     * @return bool|int
     */
    public function update(PosOrder $order, array $data)
    {
        return $order->update($this->withUpdateModificationField($data));
    }
}