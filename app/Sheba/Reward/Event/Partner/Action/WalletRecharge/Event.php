<?php namespace Sheba\Reward\Event\Partner\Action\WalletRecharge;

use App\Models\Payable;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action implements AmountCalculator
{
    private $partner;
    /**
     * @var float|int
     */
    private $rewardAmount;
    /** @var Payable $payable */
    private $payable;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet recharge event must have a amount");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->partner = $this->params[0];
        $this->payable = $this->params[1];
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
        $reward_amount = $this->rewardAmount ?: $this->reward->amount;
        return $reward_amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') for wallet recharge.';
    }

    public function calculateAmount()
    {
        $amount = ($this->payable->amount * $this->reward->amount) / 100;
        $this->rewardAmount = ($this->reward->cap && ($amount > $this->reward->cap)) ? $this->reward->cap : $amount;

        return $this->rewardAmount;
    }
}