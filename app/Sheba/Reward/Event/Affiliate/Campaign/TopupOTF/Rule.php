<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\TopupOTF;



use App\Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\Operator;
use App\Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\Quantity;
use App\Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\SimType;
use App\Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\Target;
use App\Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\TopUpStatus;
use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\TargetProgress;
use Sheba\Reward\Event\CampaignRule;

class Rule extends CampaignRule
{

    /** @var Target */
    public $target;
    /** @var Operator */
    public $operator;
    /** @var topupStatus */
    public $topupStatus;
    /** @var Quantity */
    public $quantity;
    /** @var SimType */
    public $simType;


    public function validate()
    {
        $this->operator->validate();
        $this->quantity->validate();
        $this->simType->validate();
        $this->topupStatus->validate();
    }

    public function makeParamClasses()
    {
        $this->operator = new Operator();
        $this->quantity = new Quantity();
        $this->simType = new SimType();
        $this->target = new Target();
        $this->topupStatus = new TopupStatus();
    }

    public function setValues()
    {
        $this->operator->value = property_exists($this->rule, 'operator') ? $this->rule->operator : null;
        $this->quantity->value = property_exists($this->rule, 'quantity') ? $this->rule->quantity : null;
        $this->simType->value = property_exists($this->rule, 'sim_type') ? $this->rule->sim_type : null;
        $this->topupStatus->value = property_exists($this->rule, 'topup_status') ? $this->rule->topup_status : null;
    }

    public function check(Builder $query)
    {
        // TODO: Implement check() method.
    }

    /**
     * @inheritDoc
     */
    public function getProgress(Builder $query): TargetProgress
    {
        // TODO: Implement getProgress() method.
    }
}