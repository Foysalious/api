<?php namespace Sheba\AutoSpAssign\Sorting;


use Sheba\AutoSpAssign\EligiblePartner;
use Sheba\AutoSpAssign\Sorting\Strategy\Strategy;

class PartnerSort
{
    /** @var Strategy */
    private $strategy;

    /**
     * @param Strategy $strategy
     * @return $this
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]
     */
    public function sort($partners)
    {
        return $this->strategy->sort($partners);
    }
}