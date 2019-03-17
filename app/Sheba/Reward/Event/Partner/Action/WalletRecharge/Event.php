<?php namespace Sheba\Reward\Event\Partner\Action\WalletRecharge;

use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    private $partner;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet recharge event must have a amount");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->partner = $this->params[1];
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
                return in_array($this->partner->package_id, $ids);
            }
        }

        return true;
    }

    public function getLogEvent()
    {
        $log = $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on partner id: ' . $this->partner->id;
        return $log;
    }
}