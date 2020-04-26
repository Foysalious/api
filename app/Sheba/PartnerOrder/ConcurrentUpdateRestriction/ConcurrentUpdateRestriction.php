<?php namespace Sheba\PartnerOrder\ConcurrentUpdateRestriction;

use Illuminate\Support\Facades\Facade;

class ConcurrentUpdateRestriction extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'concurrentUpdateRestriction';
    }
}