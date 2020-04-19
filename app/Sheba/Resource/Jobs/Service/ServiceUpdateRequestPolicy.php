<?php namespace Sheba\Resource\Jobs\Service;


use App\Models\Job;
use Sheba\ServiceRequest\ServiceRequestObject;

class ServiceUpdateRequestPolicy
{
    /** @var Job */
    private $job;

    /**
     * @param Job $job
     * @return ServiceUpdateRequestPolicy
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    public function existInJob(ServiceRequestObject $service)
    {
        $job_services = $this->job->jobServices->where('service_id', $service->getServiceId());
        if (count($job_services) == 0) return 0;
        if ($service->getService()->isFixed()) return 1;
        foreach ($job_services as $job_service) {
            if ($job_service->option == '[' . implode(',', $service->getOption()) . ']') return 1;
        }
        return 0;
    }

}