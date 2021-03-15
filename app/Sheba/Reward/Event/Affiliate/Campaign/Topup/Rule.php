<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\Topup;


use App\Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter\Operator;
use App\Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter\Target;
use App\Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter\TopupStatus;
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
        // TODO: Implement check() method.
    }

    public function getProgress(Builder $query): TargetProgress
    {
        // TODO: Implement getProgress() method.
    }

}