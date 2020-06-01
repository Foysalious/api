<?php namespace Sheba\Order\Policy;


use Illuminate\Support\Collection;
use Sheba\Dal\JobService\JobService;

class PreviousOrder extends Orderable
{
    /** @var Collection */
    private $jobServices;

    /**
     * @param JobService[] $jobServices
     * @return Orderable
     */
    public function setJobServices($jobServices)
    {
        $this->jobServices = $jobServices;
        return $this;
    }

    public function canOrder()
    {
        if (!$this->category->isMarketPlacePublished()) return 0;
        foreach ($this->jobServices as $jobService) {
            $location_service = $this->locationServices->where('service_id', $jobService->service_id)->first();
            if (!$location_service || !$location_service->service->isMarketPlacePublished()) return 0;
            if ($jobService->variable_type != $location_service->service->variable_type) return 0;
        }
        return 1;
    }
}