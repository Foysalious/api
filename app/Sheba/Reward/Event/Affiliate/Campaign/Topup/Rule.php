<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\Topup;


use Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter\Operator;
use Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter\Target;
use Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter\TopupStatus;
use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignRule;
use Sheba\Reward\Event\TargetProgress;

class Rule extends CampaignRule
{
    /** @var Target */
    public $target;
    /** @var Operator */
    public $operator;
    /** @var TopupStatus */
    public $topupStatus;


    public function validate()
    {
        $this->topupStatus->validate();
        $this->operator->validate();
    }

    public function makeParamClasses()
    {
        $this->target = new Target();
        $this->operator = new Operator();
        $this->topupStatus = new TopupStatus();
    }

    public function setValues()
    {
        $this->topupStatus->value = property_exists($this->rule, 'topup_status') ? $this->rule->topup_status : null;
        $this->operator->value = property_exists($this->rule, 'operator') ? $this->rule->operator : null;
    }


    public function check(Builder $query)
    {
        $this->topupStatus->check($query);
        $this->operator->check($query);
    }

    public function checkParticipation(Builder $query)
    {
        $this->topupStatus->check($query);
        $this->operator->check($query);
        $this->target->check($query);
    }

    public function getProgress(Builder $query): TargetProgress
    {
        // TODO: Implement getProgress() method.
    }

    public function isTargetAchieved($achieved_value)
    {
        return $achieved_value >= $this->target->value;
    }

    public function getAchievedValue($total_amount)
    {
        return $total_amount > $this->target->value ? $this->target->value : $total_amount ;
    }

}