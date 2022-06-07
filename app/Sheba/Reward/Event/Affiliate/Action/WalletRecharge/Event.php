<?php namespace Sheba\Reward\Event\Affiliate\Action\WalletRecharge;

use App\Models\Affiliate;
use App\Models\Payable;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Affiliate\AffiliateFilter;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action implements AmountCalculator
{
    use AffiliateFilter;

    /** @var Affiliate */
    private $affiliate;
    /** @var float|int */
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
        $this->affiliate = $this->params[0];
        $this->payable = $this->params[1];
    }

    public function isEligible()
    {
        return $this->rule->check($this->params)
            && $this->filterConstraints()
            && $this->filterTargets()
            && $this->filterByUserFilters();
    }

    public function getLogEvent()
    {
        $reward_amount = $this->rewardAmount ?: $this->reward->amount;
        return $reward_amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') for wallet recharge.';
    }

    public function calculateAmount()
    {
        $this->rewardAmount = $this->reward->calculateAmountWRTActionValue($this->payable->amount);
        return $this->rewardAmount;
    }
}