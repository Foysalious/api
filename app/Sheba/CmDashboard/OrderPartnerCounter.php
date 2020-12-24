<?php namespace Sheba\CmDashboard;

use App\Models\Partner;
use App\Models\PartnerOrder;
use Illuminate\Support\Facades\DB;

class OrderPartnerCounter
{
    /** @var null */
    private $user;

    public function forUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->makeCountData();
    }

    /**
     * @return array
     */
    private function makeCountData()
    {
        $order_partner_count = [];
        $partners = Partner::pluck('name', 'id')->toArray();

        PartnerOrder::whereHas('jobs', function ($q) use (&$order_partner_count, $partners) {
                return $q->where('crm_id', $this->user->id);
            })
            ->whereNull('closed_at')->whereNull('cancelled_at')
            ->select('partner_id', DB::raw('count(DISTINCT order_id) as count'))
            ->groupBy('partner_id')->get()
            ->each(function ($partner_order) use (&$order_partner_count, $partners) {
                $order_partner_count[$partner_order->partner_id] = [
                    'partner_name' => $partners[$partner_order->partner_id],
                    'count' => $partner_order->count
                ];
            });

        return $order_partner_count;
    }
}