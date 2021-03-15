<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\Topup\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Operator extends \Sheba\Reward\Event\CampaignEventParameter
{

    public function check(Builder $query)
    {
        // TODO: Implement check() method.
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Operator can't be empty");
    }
}