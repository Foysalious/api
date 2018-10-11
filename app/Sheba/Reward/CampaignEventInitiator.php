<?php namespace Sheba\Reward;

use Sheba\Reward\Helper\TimeFrameCalculator;

class CampaignEventInitiator extends EventInitiator
{
    private $timeFrame;

    /**
     * CampaignEventInitiator constructor.
     * @param TimeFrameCalculator $timeframe_calculator
     * @param EventDataConverter $event_data_converter
     */
    public function __construct(TimeFrameCalculator $timeframe_calculator, EventDataConverter $event_data_converter)
    {
        $this->timeFrame = $timeframe_calculator;
        parent::__construct($event_data_converter);
    }

    protected function setupEvent()
    {
        $timeFrame = $this->timeFrame->setReward($this->reward)->get();
        $this->event->setTimeFrame($timeFrame);
    }
}