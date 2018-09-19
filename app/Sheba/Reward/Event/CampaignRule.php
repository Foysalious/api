<?php namespace Sheba\Reward\Event;

use Illuminate\Database\Eloquent\Builder;

use Sheba\Reward\Exception\RulesValueMismatchException;

abstract class CampaignRule
{
    /** @var EventParameter | EventTarget */
    public $target;
    protected $rules;

    /**
     * CampaignRule constructor.
     * @param $rules
     * @throws RulesValueMismatchException
     */
    public function __construct($rules)
    {
        $this->rules = is_string($rules) ? json_decode($rules) : $rules;
        if (!property_exists($this->rules, 'target')) throw new RulesValueMismatchException('Target must be present');
        $this->makeParamClasses();
        $this->target->validate();
        $this->validate();
        $this->setValues();
        $this->target->value = $this->rules->target;
    }

    abstract public function validate();

    abstract public function makeParamClasses();

    abstract public function setValues();

    abstract public function check(Builder $query);

    /**
     * @param Builder $query
     * @return TargetProgress
     */
    abstract public function getProgress(Builder $query) : TargetProgress;
}