<?php namespace Sheba\Reward;

use App\Models\Reward;
use Sheba\Reward\Event\Campaign;

class EventInitiator
{
    private $reward;
    private $timeFrame;
    private $eventName;
    private $eventRule;
    private $eventDataConverter;

    public function __construct(TimeFrameCalculator $timeframe_calculator, EventDataConverter $event_data_converter)
    {
        $this->eventDataConverter = $event_data_converter;
        $this->timeFrame = $timeframe_calculator;
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
     * @return Campaign
     */
    public function initiate()
    {
        $event_class = $this->eventDataConverter->getEventClass($this->reward, $this->eventName);
        $rule_class = $this->eventDataConverter->getRuleClass($this->reward, $this->eventName);

        $timeFrame = $this->timeFrame->setReward($this->reward)->get();

        $rule = new $rule_class($this->eventRule);
        $event = new $event_class();
        $event->setRule($rule)->setTimeFrame($timeFrame)->setReward($this->reward);

        return $event;
    }
}