<?php namespace Sheba\Reward\Event\Partner\Campaign\OrderServed;

use Illuminate\Database\Eloquent\Builder;

use Sheba\Reward\Event\CampaignRule;
use Sheba\Reward\Event\Partner\Campaign\OrderServed\Parameter\ExcludedStatus;
use Sheba\Reward\Event\Partner\Campaign\OrderServed\Parameter\Portal;
use Sheba\Reward\Event\Partner\Campaign\OrderServed\Parameter\Target;
use Sheba\Reward\Event\TargetProgress;

class Rule extends CampaignRule
{
    /** @var Portal */
    public $portal;
    /** @var ExcludedStatus */
    public $excludedStatus;

    public function validate()
    {
        $this->excludedStatus->validate();
        $this->portal->validate();
    }

    public function makeParamClasses()
    {
        $this->excludedStatus = new ExcludedStatus();
        $this->portal = new Portal();
        $this->target = new Target();
    }

    public function setValues()
    {
        $this->excludedStatus->value = property_exists($this->rule, 'excluded_status') ? $this->rule->excluded_status : null;
        $this->portal->value = property_exists($this->rule, 'portals') ? $this->rule->portals : null;
    }

    public function check(Builder $query)
    {
        $this->excludedStatus->check($query);
        $this->portal->check($query);
        $this->target->check($query);
    }

    public function getProgress(Builder $query) : TargetProgress
    {
        $this->excludedStatus->check($query);
        $this->portal->check($query);
        $this->target->calculateProgress($query);

        return (new TargetProgress($this->target));
    }
}