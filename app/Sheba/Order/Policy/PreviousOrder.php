<?php namespace Sheba\Order\Policy;


use App\Models\Category;
use App\Models\LocationService;
use Illuminate\Support\Collection;
use Sheba\Dal\JobService\JobService;

class PreviousOrder extends Orderable
{

    /** @var Collection */
    private $jobServices;
    /** @var Category */
    protected $category;
    /** @var Collection */
    protected $locationServices;

    /**
     * @param Category $category
     * @return Orderable
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param LocationService[] $locationServices
     * @return $this
     */
    public function setLocationServices($locationServices)
    {
        $this->locationServices = $locationServices;
        return $this;
    }

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