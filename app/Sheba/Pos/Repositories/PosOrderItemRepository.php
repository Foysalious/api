<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Sheba\Repositories\BaseRepository;

class PosOrderItemRepository extends BaseRepository
{
    /**
     * @param PosOrder $order
     * @param $service_id
     * @return mixed
     */
    public function findByService(PosOrder $order, $service_id)
    {
        return $order->items->where('service_id', $service_id)->first();
    }

    /**
     * @param array $data
     * @return PosOrderItem
     */
    public function save(array $data)
    {
        return PosOrderItem::create($this->withCreateModificationField($data));
    }

    /**
     * @param PosOrderItem $item
     * @param array $data
     * @return bool|int
     */
    public function update(PosOrderItem $item, array $data)
    {
        return $item->update($this->withUpdateModificationField($data));
    }
}