<?php namespace Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignRule;
use Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter\LastUsage;
use Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter\Target;
use Sheba\Reward\Event\TargetProgress;

class Rule extends CampaignRule
{
    /** @var LastUsage */
    public $lastUsage;

    /** @var array */
    private $partnerConsecutiveCount;

    public function setPartnerConsecutiveCount($count)
    {
        $this->partnerConsecutiveCount = $count;
        return $this;
    }

    public function validate()
    {
        $this->lastUsage->validate();
    }

    public function makeParamClasses()
    {
        $this->lastUsage = new LastUsage();
        $this->target = new Target();
    }

    public function setValues()
    {
        $this->lastUsage->value = property_exists($this->rule, 'last_usage') ? $this->rule->last_usage : null;
    }

    public function check(Builder $query)
    {
        $this->checkParticipation($query);
        $this->target->setPartnerConsecutiveCount($this->partnerConsecutiveCount)->check($query);
    }

    public function checkParticipation(Builder $query)
    {
        $this->lastUsage->check($query);
    }

    public function getProgress(Builder $query): TargetProgress
    {
        $this->lastUsage->check($query);
        $this->target->calculateProgress($query);

        return (new TargetProgress($this->target));
    }

    public function isTargetAchieved($achieved_value)
    {
        return $achieved_value >= $this->target->value;
    }

    public function rampAchievedValue($achieved_value)
    {
        return min($achieved_value, $this->target->value);
    }
}