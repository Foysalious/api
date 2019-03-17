<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed;

use App\Models\Job;
use App\Models\Partner;

use App\Models\PartnerOrder;
use Illuminate\Support\Collection;

use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Action implements AmountCalculator
{
    /** @var PartnerOrder */
    private $partnerOrder;
    private $rewardAmount;

    /**
     * @param BaseRule $rule
     * @return $this | Action
     * @throws RulesTypeMismatchException
     */
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Order served event must have a order serve event rule");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->partnerOrder = $this->params[1]->calculate(true);
    }

    public function isEligible()
    {
        return $this->rule->check($this->params) &&
            $this->filterConstraints() &&
            $this->partnerOrder->hasProfit();
    }

    public function calculateAmount()
    {
        $amount = ($this->partnerOrder->profit * $this->reward->amount) / 100;
        $this->rewardAmount = ($this->reward->cap && ($amount > $this->reward->cap)) ? $this->reward->cap : $amount;

        return $this->rewardAmount;
    }

    private function filterConstraints()
    {
        $job = $this->params[0]->lastJob();
        $partner = $this->params[0]->lastJob()->partnerOrder->partner;
        $is_constraints_passed = true;

        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'App\Models\Category') {
                $is_constraints_passed = $is_constraints_passed && in_array($job->category_id, $ids);
            } elseif ($key == 'App\Models\PartnerSubscriptionPackage') {
                $is_constraints_passed = $is_constraints_passed && in_array($partner->package_id, $ids);
            }
        }

        return $is_constraints_passed;
    }

    public function getLogEvent()
    {
        $reward_amount = $this->rewardAmount ?: $this->reward->amount;
        $log = $reward_amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on ' . $this->partnerOrder->order->code();
        return $log;
    }
}