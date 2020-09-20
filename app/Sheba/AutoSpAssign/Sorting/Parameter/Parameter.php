<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


use Sheba\AutoSpAssign\EligiblePartner;

abstract class Parameter
{
    /** @var EligiblePartner */
    protected $partner;
    protected $maxValue;
    protected $minValue;


    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    public function setMinValue($minValue)
    {
        $this->minValue = $minValue;
        return $this;
    }

    public function getScore()
    {
        return $this->getValueForPartner() * ($this->getWeight() / 100);
    }

    public function setPartner(EligiblePartner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    abstract protected function getWeight();

    abstract protected function getValueForPartner();

}