<?php namespace Sheba\Reward\Event\Partner\Action\PosOrderCreate;

use App\Models\Partner;
use App\Models\PartnerPosService;
use App\Models\PosOrder;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action implements AmountCalculator
{
    /** @var Partner $partner */
    private $partner;
    /** @var float|int $payment_amount */
    private $payment_amount;
    /** @var float|int */
    private $rewardAmount;
    /** @var PosOrder $order */
    private $order;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Pos order create event must have a pos order create rule");

        return parent::setRule($rule);
    }

    /**
     * @param array $params
     * @return Action|void
     */
    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->partner = $this->params[0];
        $this->order = $this->params[1];
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
        $reward_amount = $this->rewardAmount ?: $this->reward->amount;
        return $reward_amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on pos order: ' . $this->order['id'];
    }
}