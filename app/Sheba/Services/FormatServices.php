<?php namespace Sheba\Services;

use App\Models\Job;
use Illuminate\Support\Collection;

class FormatServices
{
    private $job;
    private $services;
    private $quantity;
    private $price;


    /**
     * FormatServices constructor.
     */
    public function __construct()
    {
        $this->quantity = 0;
        $this->price = 0;
    }

    /**
     * @param Job $job
     * @return FormatServices
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function formatServices()
    {
        $this->setServices(collect([]));
        $jobServices = $this->job->jobServices->groupBy('service_id');
        foreach ($jobServices as $groupedJobServices)
        {
            if ($groupedJobServices->first()->variable_type == 'Fixed') {
                foreach ($groupedJobServices as $jobService) {
                    $this->services->push([
                        'id' => $jobService->service_id,
                        'name' => $jobService->name,
                        'service_group' => [],
                        'unit' => $jobService->service->unit,
                        'quantity' => $jobService->quantity,
                        'price' => (double)$jobService->unit_price * (double)$jobService->quantity
                    ]);
                }
            }
            else {
                $this->services->push([
                    'id' => $groupedJobServices->first()->service_id,
                    'name' => $groupedJobServices->first()->name,
                    'service_group' => $this->formatGroupedJobServices($groupedJobServices),
                    'unit' => $groupedJobServices->first()->service->unit,
                    'quantity' => $this->getQuantity(),
                    'price' => $this->getPrice()
                ]);
            }

        }
        return $this->getServices();
    }

    /**
     * @param $jobServices
     * @return Collection
     */
    private function formatGroupedJobServices($jobServices)
    {
        $services = collect([]);
        $this->quantity = 0;
        $this->price = 0;
        foreach ($jobServices as $jobService) {
            $services->push([
                'job_service_id' => $jobService->id,
                'variables' => json_decode($jobService->variables),
                'unit' => $jobService->service->unit,
                'quantity' => $jobService->quantity,
                'price' => (double)$jobService->unit_price * (double)$jobService->quantity
            ]);
            $this->quantity += $jobService->quantity;
            $this->price += (double)$jobService->unit_price * (double)$jobService->quantity;
        }
        return $services;
    }

    /**
     * @param Collection $services
     */
    public function setServices($services)
    {
        $this->services = $services;
    }

    /**
     * @return Collection
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @return double
     */
    public function getPrice()
    {
        return (double) $this->price;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
}