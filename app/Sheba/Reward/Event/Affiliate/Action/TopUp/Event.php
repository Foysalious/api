<?php namespace Sheba\Reward\Event\Affiliate\Action\TopUp;

use App\Models\Affiliate;
use App\Models\TopUpOrder;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Affiliate\AffiliateFilter;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action implements AmountCalculator
{
    use AffiliateFilter;

    /** @var Affiliate $affiliate */
    private $affiliate;

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
            throw new RulesTypeMismatchException("Affiliate top up event must have a top up rules");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->topUpOrder = $this->params[0];
        if ($this->topUpOrder->isAgentAffiliate()) {
            $this->affiliate = Affiliate::find($this->topUpOrder->agent_id);
        }
    }

    public function isEligible(): bool
    {
        return $this->rule->check($this->params)
            && $this->filterConstraints()
            && $this->filterTargets()
            && $this->filterByUserFilters();
    }

    /**
     * @return float|int|mixed
     */
    public function calculateAmount()
    {
        return $this->reward->calculateAmountWRTActionValue($this->topUpOrder->amount);
    }

    public function getLogEvent()
    {
        return $this->calculateAmount() . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on affiliate id: ' . $this->affiliate->id;
    }
}