<?php namespace Sheba\Reward;

class ActionEventInitiator extends EventInitiator
{
    protected $params;

    protected function setupEvent()
    {
        // TODO: Implement setupEvent() method.
    }

    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function initiate()
    {
        parent::initiate();
        $this->event->setParams($this->params);

        return $this->event;
    }
}