<?php namespace Sheba\Reward\Event\Customer\Action\WalletCashback;

use App\Models\PartnerOrderPayment;

use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\ParameterTypeMismatchException;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action implements AmountCalculator
{
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet cashback event must have a cashback event rule");

        return parent::setRule($rule);
    }

    public function isEligible()
    {
        return $this->rule->check($this->params);
    }

    /**
     * @return float|int|mixed
     * @throws ParameterTypeMismatchException
     */
    public function calculateAmount()
    {
        $payment = $this->params[0];
        if (!$payment instanceof PartnerOrderPayment) {
            throw new ParameterTypeMismatchException("First parameter is must be an instance of Partner Order Payment");
        }
        $amount = ($payment->amount * $this->reward->amount) / 100;

        return ($this->reward->cap && ($amount > $this->reward->cap)) ? $this->reward->cap : $amount;
    }
}