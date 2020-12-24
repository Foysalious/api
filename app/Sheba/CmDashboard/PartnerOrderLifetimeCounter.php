<?php namespace Sheba\CmDashboard;

use App\Models\User;

use Illuminate\Support\Facades\DB;

class PartnerOrderLifetimeCounter
{
    private $user;

    public function forUser(User $user)
    {
        $this->user = $user;
    }

    public function get()
    {
        return [
            'three_days'        => $this->getOrderCountsRunningFor(null, 4),
            'seven_days'        => $this->getOrderCountsRunningFor(3, 8),
            'fifteen_days'      => $this->getOrderCountsRunningFor(7, 16),
            'fifteen_plus_days' => $this->getOrderCountsRunningFor(15),
            'open_order_alt'    => $this->getOpenOrderAvgLifetime(),
            'closed_order_alt'  => $this->getClosedOrderAvgLifetime(),
        ];
    }

    private function getOrderCountsRunningFor($lower_bound = null, $upper_bound = null)
    {
        if(is_null($lower_bound) && is_null($upper_bound)) throw new \InvalidArgumentException("Both can't be null");

        $query = 'SELECT COUNT(*) as count FROM `partner_orders`';
        $query = $this->filterUser($query);
        $query .= 'partner_orders.closed_and_paid_at IS NULL AND partner_orders.cancelled_at IS NULL';
        $lifetime = 'DATEDIFF(NOW(), partner_orders.created_at)';
        if($lower_bound) {
            $query .= " AND $lifetime > $lower_bound";
        }
        if($upper_bound) {
            $query .= " AND $lifetime < $upper_bound";
        }
        $query .= ";";

        $result = DB::select(DB::raw($query));
        return $result[0]->count;
    }

    private function getClosedOrderAvgLifetime()
    {
        $query = 'SELECT AVG(DATEDIFF(partner_orders.closed_at, partner_orders.created_at)) as avg FROM `partner_orders`';
        $query = $this->filterUser($query);
        $query .= 'closed_at IS NOT NULL;';
        $result = DB::select(DB::raw($query));
        return number_format((float)($result[0]->avg ?: 0), 2);
    }

    private function getOpenOrderAvgLifetime()
    {
        $query = 'SELECT AVG(DATEDIFF(NOW(), partner_orders.created_at)) as avg FROM `partner_orders`';
        $query = $this->filterUser($query);
        $query .= 'closed_at IS NULL AND cancelled_at IS NULL;';
        $result = DB::select(DB::raw($query));
        return number_format((float)($result[0]->avg ?: 0), 2);
    }

    private function filterUser($query)
    {
        if($this->user) {
            $query .= " LEFT JOIN jobs ON jobs.partner_order_id = partner_orders.id";
            $query .= " WHERE jobs.crm_id = " . $this->user->id . " AND ";
        } else {
            $query .= " WHERE ";
        }
        return $query;
    }
}