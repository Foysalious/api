<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class RatingPointRatio extends CampaignEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->with('review');
        }
    }

    public function isCompleted(Collection $jobs, $target_value)
    {
        if (!$this->value) return true;
        $collected_points = 0;
        foreach ($jobs as $job) {
            $collected_points += $job->review ? $job->review->rating : 0;
        }
        if ($collected_points == 0) return false;
        return $collected_points >= $this->getValueCount($target_value);
    }

    public function isAchieved(Collection $jobs, $target_value)
    {
        if (!$this->value) return true;
        $collected_points = $this->getTotalRatingPoint($jobs);
        if ($collected_points == 0) return false;
        return $collected_points >= $this->getValueCount($target_value);
    }

    public function getTotalRatingPoint(Collection $jobs)
    {
        $collected_points = 0;
        foreach ($jobs as $job) {
            $collected_points += $job->review ? $job->review->rating : 0;
        }
        return $collected_points;
    }

    private function getValueCount($target_value)
    {
        return $target_value * 5 * ($this->value / 100);
    }

}