<?php namespace Sheba\AutoSpAssign\Sorting\Parameter;


use Sheba\AutoSpAssign\EligiblePartner;

abstract class Parameter
{
    /** @var EligiblePartner */
    protected $partner;

    public function getScore()
    {
        return $this->getValueForPartner() * $this->getWeight();
    }

    /**
     * @param EligiblePartner $partner
     * @return $this
     */
    public function setPartner(EligiblePartner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    abstract protected function getWeight();

    abstract protected function getValueForPartner();

}