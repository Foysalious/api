<?php namespace Sheba\Resource\Jobs\Service;


use App\Models\Job;
use App\Models\Partner;
use App\Sheba\Partner\PartnerAvailable;
use Carbon\Carbon;
use Sheba\Checkout\Partners\PartnerUnavailabilityReasons;
use Sheba\ServiceRequest\ServiceRequestObject;

class ServiceUpdateRequestPolicy
{
    /** @var Job */
    private $job;
    /** @var Partner */
    private $partner;
    private $errorMessage;

    /**
     * @param Partner $partner
     * @return ServiceUpdateRequestPolicy
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param Job $job
     * @return ServiceUpdateRequestPolicy
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param mixed $errorMessage
     * @return ServiceUpdateRequestPolicy
     */
    private function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function canUpdate()
    {
        if ($this->job->isServed()) {
            $this->setErrorMessage('Order is closed.');
            return 0;
        }
        if ($this->job->isCancelled()) {
            $this->setErrorMessage('Order is cancelled.');
            return 0;
        }
        if ($this->job->pendingCancelRequests->count() > 0) {
            $this->setErrorMessage('Order has pending cancel request');
            return 0;
        }
        return 1;
    }

    private function hasResource()
    {
        $this->partner->load(['handymanResources' => function ($q) {
            $q->selectRaw('count(distinct resources.id) as total_experts, partner_id')
                ->join('category_partner_resource', 'category_partner_resource.partner_resource_id', '=', 'partner_resource.id')
                ->where('category_partner_resource.category_id', $this->job->category_id)->groupBy('partner_id')->verified();
        }]);
        $handyman_resources = $this->partner->handymanResources->first();
        return $handyman_resources && (int)$handyman_resources->total_experts > 0 ? 1 : 0;
    }

    private function isAvailable()
    {
        return $this->partner->runningLeave(Carbon::now()) == null ? 1 : 0;
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