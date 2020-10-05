<?php namespace Sheba\Reward\Event\Customer\Action\OrderServedAndPaid;

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
    private $rewardAmount;
    private $order;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Order served abd paid event must have a order serve and paid event rule");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->order = $this->params[0]->calculate(true);
    }

    public function isEligible()
    {
        return $this->isValidCreatedDate() && $this->isOrderServedAndPaid() && $this->rule->check($this->params) && $this->filterConstraints();
    }

    private function filterConstraints()
    {
        $job = $this->params[0]->lastJob();
        $is_constraints_passed = true;

        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'Sheba\Dal\Category\Category') {
                $is_constraints_passed = $is_constraints_passed && in_array($job->category_id, $ids);
            }

            return $is_constraints_passed;
        }

        return $is_constraints_passed;
    }

    private function isOrderServedAndPaid()
    {
        $job = $this->params[0]->lastJob();
        $partner_order = $job->partnerOrder->calculate(true);

        return $job->status == constants('JOB_STATUSES')['Served'] && $partner_order->paymentStatus == "Paid";
    }

    private function isValidCreatedDate()
    {
        $order = $this->order;
        return $order->created_at->between($this->reward->start_time, $this->reward->end_time);
    }

    public function calculateAmount()
    {
        $payment_amount = $this->order->totalPrice;
        $amount = ($payment_amount * $this->reward->amount) / 100;

        $this->rewardAmount = ($this->reward->cap && ($amount > $this->reward->cap)) ? $this->reward->cap : $amount;
        return $this->rewardAmount;
    }

    public function getLogEvent()
    {
        $reward_amount = $this->rewardAmount ?: $this->reward->amount;
        $log = $reward_amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on ' . $this->order->code();
        return $log;
    }
}