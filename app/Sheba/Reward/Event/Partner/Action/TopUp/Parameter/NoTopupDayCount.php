<?php namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TopupOrder\Statuses;
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

        if (!$this->isFirstTopUpToday($topup_order)) return false;

        return $this->getNoTopUpDayBeforeToday($topup_order->agent_id) >= $this->value;
    }

    private function isFirstTopUpToday(TopUpOrder $topup_order)
    {
        $count_today = DB::table('topup_orders')
            ->where('agent_type', Partner::class)
            ->where('agent_id', $topup_order->agent_id)
            ->where('status', Statuses::SUCCESSFUL)
            ->whereDate('created_at', '<', $topup_order->created_at)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $count_today == 0;
    }

    /**
     * @param $partner_id
     * @return int
     */
    private function getNoTopUpDayBeforeToday($partner_id)
    {
        $result = DB::table('topup_orders')
            ->selectRaw('MAX(DATE(created_at)) as last_topup_date')
            ->where('agent_type', Partner::class)
            ->where('agent_id', $partner_id)
            ->whereDate('created_at', '<', Carbon::today())
            ->first();

        return Carbon::parse($result->last_topup_date)->diffInDays(Carbon::now()) - 1;
    }
}