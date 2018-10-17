<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed;

use App\Models\Job;
use App\Models\Partner;

use Illuminate\Support\Collection;

use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Action implements AmountCalculator
{
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Order served event must have a order serve event rule");

        return parent::setRule($rule);
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

    public function isEligible()
    {
        return $this->rule->check($this->params) && $this->filterConstraints();
    }

    public function calculateAmount()
    {
        $payment_amount = $this->params[0]->totalPrice;
        $amount = ($payment_amount * $this->reward->amount) / 100;

        return ($this->reward->cap && ($amount > $this->reward->cap)) ? $this->reward->cap : $amount;
    }
}