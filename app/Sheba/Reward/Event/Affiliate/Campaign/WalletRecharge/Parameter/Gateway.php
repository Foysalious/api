<?php


namespace App\Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Gateway extends CampaignEventParameter
{

    public function check(Builder $query)
    {

    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Gateway can't be empty");
    }
}