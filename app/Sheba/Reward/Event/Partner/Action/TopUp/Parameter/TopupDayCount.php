<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Sheba\Reward\Event\ActionEventParameter;

class TopupDayCount extends ActionEventParameter
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
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as day_count')
            ->where('agent_type', Partner::class)
            ->where('agent_id', $topup_order->agent_id)
            ->get();

        return $result->day_count >= $this->value->min && $result->day_count <= $this->value->max;
    }
}