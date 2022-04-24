<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp;

use App\Models\Partner;

use App\Models\TopUpOrder;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Partner\Action\PartnerFilter;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action implements AmountCalculator
{
    use PartnerFilter;

    /** @var Partner $partner */
    private $partner;

    /** @var TopUpOrder $topUpOrder */
    private $topUpOrder;

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
        $this->topUpOrder = $this->params[0];
        if($this->topUpOrder->isAgentPartner())
            $this->partner = Partner::find($this->topUpOrder->agent_id);
    }

    public function isEligible(): bool
    {
        return $this->rule->check($this->params)
            && $this->filterConstraints()
            && $this->filterTargets()
            && $this->filterByUserFilters();
    }

    /**
     * @return bool
     */
    private function filterConstraints(): bool
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'App\Models\PartnerSubscriptionPackage')
                return in_array($this->partner->package_id, $ids);

        }

        return true;
    }

    /**
     * @return bool
     */
    private function filterTargets(): bool
    {
        if(count($this->reward->rewardTargets) === 0) return true;
        $reward_target = $this->reward->rewardTargets()->where('target_id', $this->partner->id)->first();
        return (isset($reward_target));
    }

    /**
     * @return float|int|mixed
     */
    public function calculateAmount()
    {
        if (!$this->reward->is_amount_percentage) return $this->reward->amount;

        $amount = ($this->reward->amount * $this->topUpOrder->amount) / 100;
        return $this->reward->cap ? min($amount, $this->reward->cap) : $amount;
    }

    public function getLogEvent()
    {
        $log = $this->calculateAmount() . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on partner id: ' . $this->partner->id;
        return $log;
    }
}