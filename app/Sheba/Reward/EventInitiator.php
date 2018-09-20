<?php namespace Sheba\Reward;

use App\Models\Reward;
use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Campaign;

abstract class EventInitiator
{
    protected $reward;
    /** @var Action | Campaign */
    protected $event;
    private $eventName;
    private $eventRule;
    private $eventDataConverter;

    public function __construct(EventDataConverter $event_data_converter)
    {
        $this->eventDataConverter = $event_data_converter;
    }

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    public function setName($event_name)
    {
        $this->eventName = $event_name;
        return $this;
    }

    public function setRule($event_rule)
    {
        $this->eventRule = $event_rule;
        return $this;
    }

    /**
     * @return Action|Campaign
     */
    public function initiate()
    {
        $event_class = $this->eventDataConverter->getEventClass($this->reward, $this->eventName);
        $rule_class = $this->eventDataConverter->getRuleClass($this->reward, $this->eventName);
        $rule = new $rule_class($this->eventRule);
        $this->event = new $event_class();
        $this->setupEvent();
        $this->event->setRule($rule)->setReward($this->reward);
        return $this->event;
    }

    abstract protected function setupEvent();
}