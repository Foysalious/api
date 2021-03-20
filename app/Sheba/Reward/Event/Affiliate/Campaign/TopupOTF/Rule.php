<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\TopupOTF;



use Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\Operator;
use Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\Quantity;
use Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\SimType;
use Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\Target;
use Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter\TopupStatus;
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
        $this->topupStatus->check($query);
        $this->operator->check($query);
        $this->simType->check($query);
        $this->target->check($query);

    }

    public function checkParticipation(Builder $query)
    {
        $this->quantity->check($query);
        $this->topupStatus->check($query);
        $this->operator->check($query);
        $this->simType->check($query);
        $this->target->check($query);
    }



    /**
     * @inheritDoc
     */
    public function getProgress(Builder $query): TargetProgress
    {
        // TODO: Implement getProgress() method.
    }

    public function isTargetAchieved($achieved_value)
    {
        return $achieved_value >= $this->quantity->value;
    }

    public function getAchievedValue($quantity)
    {
        return $quantity > $this->quantity->value ? $this->quantity->value : $quantity ;
    }
}