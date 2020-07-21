<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Rating extends CampaignEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->whereHas('review', function ($q) {
                $q->where('rating', $this->value);
            });
        }
    }
}