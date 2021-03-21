<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Quantity extends CampaignEventParameter
{

    public function check(Builder $query)
    {
        $query->having('quantity', '>=', $this->value);
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Quantity can't be empty");
    }
}