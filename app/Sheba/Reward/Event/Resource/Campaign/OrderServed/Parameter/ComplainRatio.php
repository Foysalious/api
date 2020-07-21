<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ComplainRatio extends CampaignEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(Builder $query)
    {
        if (!$this->value) return;
        $query->with('complains');
    }

    public function isCompleted(Collection $jobs, $target_value)
    {
        if (!$this->value) return true;
        $total_complains = 0;
        foreach ($jobs as $job) {
            $total_complains += count($job->complains);
        }
        if ($total_complains == 0) return true;
        return $total_complains <= $this->getValueCount($target_value);
    }

    public function isAchieved(Collection $jobs, $target_value)
    {
        if (!$this->value) return true;
        $total_complains = $this->getTotalComplains($jobs);
        if ($total_complains == 0) return true;
        return $total_complains <= $this->getValueCount($target_value);
    }

    public function getTotalComplains(Collection $jobs)
    {
        $total_complains = 0;
        foreach ($jobs as $job) {
            $total_complains += count($job->complains);
        }
        return $total_complains;
    }

    private function getValueCount($target_value)
    {
        return ($this->value * $target_value) / 100;
    }
}