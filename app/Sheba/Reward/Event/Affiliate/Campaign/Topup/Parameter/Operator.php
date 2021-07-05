<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Operator extends CampaignEventParameter
{

    public function check(Builder $query)
    {

        if ($this->value != null) {
            $query->whereIn('topup_orders.vendor_id', $this->value );
        }
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Operator can't be empty");
    }
}