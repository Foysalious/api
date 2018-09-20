<?php namespace Sheba\Reward\Event\Partner\Campaign\OrderServed\Parameter;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use DB;

class ExcludedStatus extends CampaignEventParameter
{

    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->whereDoesntHave('statusChangeLog', function ($q) {
                $q->whereIn('to_status', $this->value);
            });
        }
    }
}