<?php namespace Sheba\Pos\Repositories;

use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Illuminate\Database\Eloquent\Model;
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

    public function getModel(){
        return new PosOrderItem();
    }

}
