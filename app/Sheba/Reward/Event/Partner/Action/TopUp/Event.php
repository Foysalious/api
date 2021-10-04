<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action implements AmountCalculator
{
    /** @var Partner $partner */
    private $partner;
    /**
     * @var TopUpOrder $topup_order
     */
    private $topup_order;

    /**
     * @param BaseRule $rule
     * @return Action
     * @throws RulesTypeMismatchException
     */
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Partner daily usage event must have a daily usage rules");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->topup_order = $this->params[0];
    }

    public function isEligible()
    {
        return $this->rule->check($this->params) && $this->filterConstraints();
    }

    private function filterConstraints()
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'App\Models\PartnerSubscriptionPackage') {
                if($this->topup_order->isAgentPartner()) {
                    $this->partner = Partner::find($this->topup_order->agent_id);
                    return in_array($this->partner->package_id, $ids);
                }
            }
        }

        return true;
    }

    /**
     * @return float|int|mixed
     */

    public function calculateAmount()
    {
        $topUpAmount = $this->params[0];
        if ($this->reward->is_amount_percentage) {

            return (($this->reward->amount * $topUpAmount->amount) / 100);
        }
        return $this->reward->amount;
    }

    public function getLogEvent()
    {
        $log = $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on partner id: ' . $this->partner->id;
        return $log;
    }
}
