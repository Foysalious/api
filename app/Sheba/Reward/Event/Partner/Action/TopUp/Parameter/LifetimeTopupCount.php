<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Sheba\Reward\Event\ActionEventParameter;

class LifetimeTopupCount extends ActionEventParameter
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

        /** @var Partner $agent */
        $agent = $topup_order->agent;
        $lifetime_count = $agent->topUpOrders()->successful()->count();

        return $lifetime_count == $this->value;
    }
}