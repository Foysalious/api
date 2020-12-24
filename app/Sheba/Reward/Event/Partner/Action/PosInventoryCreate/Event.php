<?php namespace Sheba\Reward\Event\Partner\Action\PosInventoryCreate;

use App\Models\Partner;
use App\Models\PartnerPosService;

use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action implements AmountCalculator
{
    /** @var PartnerPosService $partnerPosService */
    private $partnerPosService;
    /** @var Partner $partner */
    private $partner;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Partner pos inventory create event must have a pos inventory create rules");

        return parent::setRule($rule);
    }

    public function setParams(array $params)
    {
        parent::setParams($params);
        $this->partner = $this->params[0];
        $this->partnerPosService = $this->params[1];
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