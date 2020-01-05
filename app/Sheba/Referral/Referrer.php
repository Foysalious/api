<?php namespace Sheba\Referral;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Referrer
{
    /** @var Collection */
    public $refers;
    /** @var HasReferrals */
    protected $referrer;

    abstract function getReferrals(Request $request): Collection;
    abstract function totalIncome(Request $request);
    abstract function totalRefer();
    abstract function details($id);
    abstract function totalSuccessfulRefer();
    protected function init()
    {

        return $this->refers = $this->referrer->referrals();
    }
    abstract function store(Request $request);

}
