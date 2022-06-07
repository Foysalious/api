<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\TopupOrder\Statuses;

class TopUpCounts
{
    /**
     * @param  TopUpOrder  $topup_order
     * @return bool
     */
    public static function isFirstTopUpTodayForAgent(TopUpOrder $topup_order): bool
    {
        $count_today = DB::table('topup_orders')
            ->where('agent_type', $topup_order->agent_type)
            ->where('agent_id', $topup_order->agent_id)
            ->where('status', Statuses::SUCCESSFUL)
            ->where('created_at', '<', $topup_order->created_at)
            ->whereDate('created_at', '=', Carbon::today())
            ->count();

        return $count_today == 0;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return int
     */
    public static function getNoTopUpDayBeforeTodaysTopUp(TopUpOrder $topup_order): int
    {
        return self::getNoTopUpDayBeforeToday($topup_order->agent);
    }

    /**
     * @param TopUpAgent $agent
     * @return int
     */
    public static function getNoTopUpDayBeforeToday(TopUpAgent $agent): int
    {
        $result = DB::table('topup_orders')
            ->selectRaw('MAX(DATE(created_at)) as last_topup_date')
            ->where('agent_type', get_class($agent))
            ->where('agent_id', $agent->id)
            ->where('status', Statuses::SUCCESSFUL)
            ->whereDate('created_at', '<', Carbon::today())
            ->first();

        return Carbon::parse($result->last_topup_date)->diffInDays(Carbon::now()) - 1;
    }

    /**
     * @param TopUpOrder $topup_order
     * @return int
     */
    public static function getDayCountBeforeTodaysTopUp(TopUpOrder $topup_order): int
    {
        return self::getDayCountBeforeToday($topup_order->agent);
    }

    /**
     * @param TopUpAgent $agent
     * @return int
     */
    public static function getDayCountBeforeToday(TopUpAgent $agent): int
    {
        $result = DB::table('topup_orders')
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as day_count')
            ->where('agent_type', get_class($agent))
            ->where('agent_id', $agent->id)
            ->where('status', Statuses::SUCCESSFUL)
            ->whereDate('created_at', '<', Carbon::today())
            ->first();

        return $result->day_count;
    }

    /**
     * @param TopUpOrder  $topup_order
     * @param $n
     * @return bool
     */
    public static function isNthTopUpByAgent(TopUpOrder $topup_order, $n)
    {
        $agent = $topup_order->agent;
        return $agent->topUpOrders()->successful()->count() == $n;
    }
}