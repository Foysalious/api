<?php namespace Sheba\Repositories;

use App\Models\JobStatusChangeLog;
use App\Models\Partner;
use App\Models\PartnerOrder;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use DB;
use Sheba\Helpers\TimeFrame;

class PartnerOrderRepository
{
    public function getTodayClosedOrders(Partner $partner = null)
    {
        if ($partner) return $this->getTodayClosedOrdersByPartner($partner);
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
        if ($partner) return $this->getClosedOrdersOfDateByPartner($date, $partner);
        return $this->calculate($this->closedQuery($date)->get());
    }

    public function getClosedOrdersBetween(TimeFrame $time_frame, Partner $partner = null)
    {
        if ($partner) return $this->getClosedOrdersBetweenDateByPartner($time_frame, $partner);
        return $this->calculate($this->closedQueryBetween($time_frame)->get());
    }

    public function getAcceptedOrdersBetween(TimeFrame $time_frame, Partner $partner = null)
    {
        if ($partner) return $this->getAcceptedOrdersBetweenDateByPartner($time_frame, $partner)->groupBy('partner_orders.partner_id')->first();
    }

    public function getClosedOrdersOfDateByPartner(Carbon $date, Partner $partner)
    {
        return $this->calculate($this->closedQuery($date)->of($partner->id)->get());
    }

    public function getClosedOrdersBetweenDateByPartner(TimeFrame $time_frame, Partner $partner)
    {
        return $this->calculate($this->closedQueryBetween($time_frame)->of($partner->id)->get());
    }

    public function getAcceptedOrdersBetweenDateByPartner(TimeFrame $time_frame, Partner $partner)
    {
        return JobStatusChangeLog::join('jobs', 'jobs.id', '=', 'job_status_change_logs.job_id')
            ->join('partner_orders', 'partner_orders.id', '=', 'jobs.partner_order_id')
            ->where('to_status', constants('JOB_STATUSES')['Accepted'])
            ->where('partner_orders.partner_id', $partner->id)
            ->whereBetween('job_status_change_logs.created_at', $time_frame->getArray())
            ->select('partner_orders.partner_id', DB::raw('count(*) as count'));
    }

    public function getClosedOrdersOfDateGroupedByPartner(Carbon $date, $partners = null)
    {
        $orders_by_partner = $this->closedQueryByPartners($date, $partners)->get()->groupBy('partner_id');
        return $orders_by_partner->map(function ($partner_orders) {
            return $this->calculate($partner_orders);
        });
    }

    private function closedQueryByPartners(Carbon $date, $partners)
    {
        $orders_by_partner = $this->closedQuery($date);
        if ($partners instanceof Collection) $partners = $partners->pluck('id')->toArray();
        if (!empty($partners)) $orders_by_partner = $orders_by_partner->of($partners);
        return $orders_by_partner;
    }

    private function closedQuery(Carbon $date)
    {
        return $this->priceQuery()->closedAt($date);
    }

    private function closedQueryBetween(TimeFrame $time_frame)
    {
        return $this->priceQuery()->closedAtBetween($time_frame);
    }

    private function priceQuery()
    {
        return PartnerOrder::with('order', 'jobs.usedMaterials');
    }

    private function calculate(Collection $partner_orders)
    {
        return $partner_orders->map(function ($order) {
            return $order->calculate($price_only = true);
        });
    }
}