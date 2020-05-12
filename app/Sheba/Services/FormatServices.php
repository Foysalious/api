<?php namespace Sheba\Services;

use Illuminate\Support\Collection;

class FormatServices
{
    private $jobServices;
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
     * @param $jobServices
     * @return FormatServices
     */
    public function setJobServices($jobServices)
    {
        $this->jobServices = $jobServices->groupBy('service_id');
        return $this;
    }

    public function formatServices()
    {
        $this->setServices(collect([]));
        foreach ($this->jobServices as $groupedJobServices)
        {
            if ($groupedJobServices->first()->variable_type == 'Fixed') {
                foreach ($groupedJobServices as $jobService) {
                    $this->services->push([
                        'service_id' => $jobService->service_id,
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
                    'service_id' => $groupedJobServices->first()->service_id,
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
                'id' => $jobService->id,
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