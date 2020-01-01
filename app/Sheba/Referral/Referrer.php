<?php namespace Sheba\Referral;

use Illuminate\Support\Collection;

abstract class Referrer
{
    /** @var HasReferrals */
    protected $referrer;
    /** @var Collection */
    public $refers;



    protected function init()
    {

        return $this->refers = $this->referrer->referrals()
            ->select(['referrer_income','refer_level']);
    }
    abstract function getReferrals():Collection;

}
