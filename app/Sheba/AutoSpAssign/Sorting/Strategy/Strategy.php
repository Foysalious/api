<?php namespace Sheba\AutoSpAssign\Sorting\Strategy;


use Sheba\AutoSpAssign\EligiblePartner;

interface Strategy
{
    /**
     * @param EligiblePartner[] $partners
     * @return EligiblePartner[]
     */
    public function sort($partners);
}