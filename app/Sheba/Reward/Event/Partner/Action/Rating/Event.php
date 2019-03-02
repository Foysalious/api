<?php namespace Sheba\Reward\Event\Partner\Action\Rating;

use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    private $order;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Rating event must have a rate");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->order = $this->params[0]->job->partnerOrder->order;
    }

    public function isEligible()
    {
        return $this->rule->check($this->params) && $this->filterConstraints();
    }

    private function filterConstraints()
    {
        $job = $this->params[0]->job;
        $partner = $this->params[0]->job->partnerOrder->partner;
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
        $log = $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on ' . $this->order->code();
        return $log;
    }
}