<?php namespace Sheba\Repositories;

use App\Models\Partner;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PartnerOrderRepository
{
    public function getTodayClosedOrders(Partner $partner = null)
    {
        if($partner) return $this->getTodayClosedOrdersByPartner($partner);
        return $this->getClosedOrdersOf(Carbon::today());
    }

    public function getTodayClosedOrdersByPartner(Partner $partner)
    {
        return $this->getClosedOrdersOfDateByPartner(Carbon::today(), $partner);
    }

    public function getTodayClosedOrdersGroupedByPartner($partners = null)
    {
        return $this->getClosedOrdersOfDateGroupedByPartner(Carbon::today(), $partners);
    }

    public function getClosedOrdersOf(Carbon $date, Partner $partner = null)
    {
        if($partner) return $this->getClosedOrdersOfDateByPartner($date, $partner);
        return $this->calculate($this->closedQuery($date)->get());
    }

    public function getClosedOrdersOfDateByPartner(Carbon $date, Partner $partner)
    {
        return $this->calculate( $this->closedQuery($date)->of($partner->id)->get() );
    }

    public function getClosedOrdersOfDateGroupedByPartner(Carbon $date, $partners = null)
    {
        $orders_by_partner = $this->closedQueryByPartners($date, $partners)->get()->groupBy('partner_id');
        return $orders_by_partner->map(function($partner_orders) {
            return $this->calculate($partner_orders);
        });
    }

    private function closedQueryByPartners(Carbon $date, $partners)
    {
        $orders_by_partner = $this->closedQuery($date);
        if($partners instanceof Collection) $partners = $partners->pluck('id')->toArray();
        if(!empty($partners)) $orders_by_partner = $orders_by_partner->of($partners);
        return $orders_by_partner;
    }

    private function closedQuery(Carbon $date)
    {
        return $this->priceQuery()->closedAt($date);
    }

    private function priceQuery()
    {
        return PartnerOrder::with('order', 'jobs.usedMaterials');
    }

    private function calculate(Collection $partner_orders)
    {
        return $partner_orders->map(function($order) {
            return $order->calculate($price_only = true);
        });
    }
}