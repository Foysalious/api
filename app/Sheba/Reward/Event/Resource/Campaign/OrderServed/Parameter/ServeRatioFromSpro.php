<?php namespace Sheba\Reward\Event\Resource\Campaign\OrderServed\Parameter;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Sheba\Jobs\JobStatuses;
use Sheba\Reward\Event\CampaignEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ServeRatioFromSpro extends CampaignEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(Builder $query)
    {
        if ($this->value != null) {
            $query->with(['statusChangeLogs' => function ($q) {
                $q->where('to_status', JobStatuses::SERVED)->where('portal_name', 'resource-app');
            }]);
        }
    }

    public function isCompleted(Collection $jobs, $target_value)
    {
        if (!$this->value) return true;
        $total_served_jobs_from_resource_app = 0;
        foreach ($jobs as $job) {
            $total_served_jobs_from_resource_app += count($job->statusChangeLogs);
        }
        if ($total_served_jobs_from_resource_app == 0) return false;
        return $total_served_jobs_from_resource_app >= $this->getValueCount($target_value);
    }

    public function isAchieved(Collection $jobs, $target_value)
    {
        if (!$this->value) return true;
        $total_served_jobs_from_resource_app = $this->getTotalServedFromSpro($jobs);
        if ($total_served_jobs_from_resource_app == 0) return false;
        return $total_served_jobs_from_resource_app >= $this->getValueCount($target_value);
    }

    private function getValueCount($target_value)
    {
        return ($this->value * $target_value) / 100;
    }

    public function getTotalServedFromSpro(Collection $jobs)
    {
        $total_served_jobs_from_resource_app = 0;
        foreach ($jobs as $job) {
            $total_served_jobs_from_resource_app += count($job->statusChangeLogs);
        }
        return $total_served_jobs_from_resource_app;
    }
}