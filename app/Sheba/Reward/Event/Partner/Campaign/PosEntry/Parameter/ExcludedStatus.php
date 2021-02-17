<?php

namespace Sheba\Reward\Event\Partner\Campaign\PosEntry\Parameter;

use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use DB;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ExcludedStatus extends CampaignEventParameter
{

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->whereDoesntHave('statusChangeLogs', function ($q) {
                $q->whereIn('to_status', $this->value);
            });
        }
    }
}