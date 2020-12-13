<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp;

use App\Models\Partner;
use App\Models\PartnerPosService;

use App\Models\Payable;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action implements AmountCalculator
{
    /** @var Partner $partner */
    private $partner;
    private $requestFrom;

    /**
     * @param BaseRule $rule
     * @return Action
     * @throws RulesTypeMismatchException
     */
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Partner daily usage event must have a daily usage rules");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        \Log::info(json_encode($params));
        parent::setParams($params);
        $this->partner = $this->params[0];
    }

    public function isEligible()
    {
        return $this->rule->check($this->params);
    }

    /**
     * @return float|int|mixed
     */
    public function calculateAmount()
    {
        if ($this->reward->is_amount_percentage) {
            \Log::info('amount ----->', $this->reward->amount * $this->params->amount);
            return $this->reward->amount * $this->params->amount;
        }
        return $this->reward->amount;
    }

    public function getLogEvent()
    {
        $log = $this->reward->amount . ' ' . $this->reward->type . ' credited for ' . $this->reward->name . '(' . $this->reward->id . ') on partner id: ' . $this->partner->id;
        return $log;
    }
}