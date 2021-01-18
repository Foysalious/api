<?php

namespace Sheba\Reward\Event\Partner\Campaign\DueTrackerEntry\Parameter;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Portal extends CampaignEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Portal can't be empty");
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->whereHas('statusChangeLogs', function ($q) {
                $q->where('to_status', 'Served')->whereIn('portal_name', $this->value);
            });
        }
    }
}