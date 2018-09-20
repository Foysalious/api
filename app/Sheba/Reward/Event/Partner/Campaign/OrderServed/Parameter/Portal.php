<?php namespace Sheba\Reward\Event\Partner\Campaign\OrderServed\Parameter;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;

class Portal extends CampaignEventParameter
{
    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->whereHas('statusChangeLog', function ($q) {
                $q->where('to_status', 'Served')->whereIn('portal_name', $this->value);
            });
        }
    }
}