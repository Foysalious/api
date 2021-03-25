<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class TopupStatus extends CampaignEventParameter
{

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->where('topup_orders.status', $this->value );
        }
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("TopUp status can't be empty");
    }
}