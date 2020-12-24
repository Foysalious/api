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
    abstract function totalIncome();
    abstract function totalRefer();
    abstract function details($id);
    abstract function totalSuccessfulRefer();

    abstract public function home();

    protected function init()
    {
        return $this->refers = $this->referrer->referrals();
    }
    abstract function store(Request $request);

}
