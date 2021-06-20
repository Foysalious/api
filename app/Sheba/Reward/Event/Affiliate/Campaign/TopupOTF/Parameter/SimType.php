<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\TopupOTF\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class SimType extends CampaignEventParameter
{

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->where('payee_mobile_type', $this->value );
        }
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Sim Type can't be empty");
    }
}