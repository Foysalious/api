<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Reward\Event\ActionEventParameter;

class TopupDayCount extends ActionEventParameter
{
    use DailyFirstTopUpChecker;

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

        $day_count = $this->getDayCountBeforeToday($topup_order->agent_id);

        return $day_count >= $this->value->min && $day_count <= $this->value->max;
    }

    /**
     * @param $partner_id
     * @return int
     */
    private function getDayCountBeforeToday($partner_id)
    {
        $result = DB::table('topup_orders')
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as day_count')
            ->where('agent_type', Partner::class)
            ->where('agent_id', $partner_id)
            ->where('status', Statuses::SUCCESSFUL)
            ->whereDate('created_at', '<', Carbon::today())
            ->first();

        return $result->day_count;
    }
}