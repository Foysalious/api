<?php namespace Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter;

use App\Models\Affiliate;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Reward\Event\ActionEventParameter;

class NoTopupDayCount extends ActionEventParameter
{
    public function validate(){}

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if ($this->value == null) return true;

        /** @var TopUpOrder $topup_order */
        $topup_order = $params[0];

        $result = DB::table('topup_orders')
            ->selectRaw('MAX(DATE(created_at)) as last_topup_date')
            ->where('agent_type', Affiliate::class)
            ->where('agent_id', $topup_order->agent_id)
            ->get();

        $no_topup_day = Carbon::parse($result->last_topup_date)->diffInDays(Carbon::now()) - 1;

        return $no_topup_day == $this->value;
    }
}