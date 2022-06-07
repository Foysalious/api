<?php namespace Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter;

use App\Models\TopUpOrder;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\TopUp\TopUpCounts;

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

        return TopUpCounts::isNthTopUpByAgent($topup_order, $this->value);
    }
}