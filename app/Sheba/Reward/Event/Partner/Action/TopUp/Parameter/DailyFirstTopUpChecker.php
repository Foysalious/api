<?php namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TopupOrder\Statuses;

trait DailyFirstTopUpChecker
{
    /**
     * @param TopUpOrder $topup_order
     * @return bool
     */
    private function isFirstTopUpToday(TopUpOrder $topup_order)
    {
        $count_today = DB::table('topup_orders')
            ->where('agent_type', Partner::class)
            ->where('agent_id', $topup_order->agent_id)
            ->where('status', Statuses::SUCCESSFUL)
            ->where('created_at', '<', $topup_order->created_at)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $count_today == 0;
    }
}