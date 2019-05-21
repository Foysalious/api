<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PosOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

    public function getTodayOrdersByPartner(Partner $partner)
    {
        return $this->getOrdersOfDateByPartner(Carbon::today(), $partner);
    }

    private function getOrdersOfDateByPartner(Carbon $date, Partner $partner)
    {
        return $this->calculate($this->createdQuery($date)->of($partner->id)->get());
    }

    private function createdQuery(Carbon $date)
    {
        return $this->priceQuery()->createdAt($date);
    }

    private function priceQuery()
    {
        return PosOrder::with('items');
    }

    private function calculate(Collection $pos_orders)
    {
        return $pos_orders->map(function ($order) {
            return $order->calculate();
        });
    }
}