<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Gmv extends CampaignEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(Builder $query)
    {
        if (!$this->value) return;
        $query->with('partnerOrder.order');
    }

    public function isCompleted(Collection $jobs)
    {
        if (!$this->value) return true;
        $collected_gmv = 0;
        foreach ($jobs as $job) {
            $job->partnerOrder->calculate(1);
            $collected_gmv += $job->partnerOrder->gmv;
        }
        return $collected_gmv >= $this->value;
    }

    public function isAchieved(Collection $jobs)
    {
        if (!$this->value) return true;
        $collected_gmv = $this->getTotalGmv($jobs);
        if ($collected_gmv == 0) return false;
        return $collected_gmv >= $this->value;
    }


    public function getTotalGmv(Collection $jobs)
    {
        $collected_gmv = 0;
        foreach ($jobs as $job) {
            $job->partnerOrder->calculate(1);
            $collected_gmv += $job->partnerOrder->gmv;
        }
        return $collected_gmv;
    }
}