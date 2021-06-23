<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Gateway extends CampaignEventParameter
{

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $raw = '( ';
            foreach ($this->value as $key=>$each ){
                if($key == 0){
                    $raw .= 'payments.transaction_id like "%' . $each . '%" ';
                } else {
                    $raw .= ' or payments.transaction_id like "%' . $each . '%"';
                }
            }
            $raw .= ' )';
            $query->whereRaw($raw);
        }

    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Gateway can't be empty");
    }
}