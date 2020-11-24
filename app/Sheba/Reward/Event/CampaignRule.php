<?php namespace Sheba\Reward\Event;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Exception\RulesValueMismatchException;

abstract class CampaignRule extends Rule
{
    /** @var CampaignEventParameter | EventTarget */
    public $target;

    /**
     * CampaignRule constructor.
     * @param $rule
     * @param array $params
     * @throws RulesValueMismatchException
     */
    public function __construct($rule, $params)
    {
        parent::__construct($rule, $params);

        if (!property_exists($this->rule, 'target')) throw new RulesValueMismatchException('Target must be present');
        $this->target->value = $this->rule->target;
        $this->target->validate();
    }

    abstract public function check(Builder $query);

    /**
     * @param Builder $query
     * @return TargetProgress
     */
    abstract public function getProgress(Builder $query) : TargetProgress;
}