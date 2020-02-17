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
    public function findFromOrder(PosOrder $order,$id){
        return $order->items->where('id',$id)->first();
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
     * @return PosOrderItem
     */
    public function getModel(){
        return new PosOrderItem();
    }
}
