<?php namespace Sheba\Pos\Repositories;

use App\Models\Partner;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Helpers\TimeFrame;
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

    /**
     * @param TimeFrame $time_frame
     * @param Partner|null $partner
     * @return Collection
     */
    public function getCreatedOrdersBetween(TimeFrame $time_frame, Partner $partner = null)
    {
        if ($partner) return $this->getCreatedOrdersBetweenDateByPartner($time_frame, $partner);
        return $this->calculate($this->createdQueryBetween($time_frame)->get());
    }

    /**
     * @param TimeFrame $time_frame
     * @param Partner|null $partner
     * @param PosCustomer|null $customer
     * @return Collection
     */
    public function getCreatedOrdersBetweenByPartnerAndCustomer(TimeFrame $time_frame, Partner $partner = null, PosCustomer $customer = null)
    {
        return $this->getCreatedOrdersBetweenDateByPartnerAndCustomer($time_frame, $partner, $customer);
    }

    public function getCreatedOrdersBetweenDateByPartner(TimeFrame $time_frame, Partner $partner)
    {
        return $this->calculate($this->createdQueryBetween($time_frame)->of($partner->id)->get());
    }

    public function getCreatedOrdersBetweenDateByPartnerAndCustomer(TimeFrame $time_frame, Partner $partner, PosCustomer $customer)
    {
        return $this->calculate($this->createdQueryBetween($time_frame)->of($partner->id)->OfCustomer($customer->id)->get());
    }

    private function createdQueryBetween(TimeFrame $time_frame)
    {
        return $this->priceQuery()->createdAtBetween($time_frame);
    }

    private function getOrdersOfDateByPartner(Carbon $date, Partner $partner)
    {
        return $this->calculate($this->createdQuery($date)->of($partner->id)->get());
    }

    public function getCreatedWebstoreOrdersBetweenDateByPartner(TimeFrame $time_frame, Partner $partner)
    {
        return $this->calculate($this->createdQueryBetween($time_frame)->of($partner->id)->webstoreOrders()->get());
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

    /**
     * @param Carbon $date
     * @param null $partners
     * @return mixed
     */
    public function getCreatedOrdersOfDateGroupedByPartner(Carbon $date, $partners = null)
    {
        $orders_by_partner = $this->createdQueryByPartners($date, $partners)->get()->groupBy('partner_id');
        return $orders_by_partner->map(function($pos_orders) {
            return $this->calculate($pos_orders);
        });
    }

    /**
     * @param Carbon $date
     * @param $partners
     * @return mixed
     */
    private function createdQueryByPartners(Carbon $date, $partners)
    {
        $orders_by_partner = $this->createdQuery($date);
        if ($partners instanceof Collection) $partners = $partners->pluck('id')->toArray();
        if (!empty($partners)) $orders_by_partner = $orders_by_partner->of($partners);
        return $orders_by_partner;
    }
}
