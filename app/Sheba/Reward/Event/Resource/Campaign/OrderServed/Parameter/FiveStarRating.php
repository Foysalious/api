<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class FiveStarRating extends CampaignEventParameter
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
                $q->where('rating', 5);
                if ($this->value == 'With Complement') {
                    $q->whereHas('rates', function ($q) {
                        $q->where('rate_answer_text', null);
                    });
                }
            });
        }
    }
}