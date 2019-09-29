<?php namespace Sheba\Reward\Event\Partner\Action\PartnerCreationBonus;

use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action implements AmountCalculator
{
    private $partner;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Partner creation bonus event must have a Partner creation bonus rules");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->partner = $this->params[0];
    }

    public function isEligible()
    {
        return $this->rule->check($this->params) && $this->filterConstraints();
    }

    private function filterConstraints()
    {
        $package_id = $this->params[0]->package_id;

        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'App\Models\PartnerSubscriptionPackage') {
                return in_array($package_id, $ids);
            }
        }

        return true;
    }

    /**
     * @return float|int|mixed
     */
    public function calculateAmount()
    {
        return $this->reward->amount;
    }

    public function getLogEvent()
    {
        $log = $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on partner id: ' . $this->partner->id;
        return $log;
    }
}