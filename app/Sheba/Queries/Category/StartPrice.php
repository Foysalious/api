<?php

namespace App\Sheba\Queries\Category;

use App\Models\Category;
use App\Models\Partner;
use Carbon\Carbon;

class StartPrice
{
    public $category;
    public $price;
    public $location_id;

    public function __construct($category, $location_id)
    {
        $this->category = $category instanceof Category ? $category : Category::find((int)$category);
        $this->location_id = $location_id ? $location_id : 4;
    }

    public function calculate()
    {
        try {
            $services = collect();
            if ($this->isMasterCategory()) {
                $this->category = $this->loadServices($this->category->children);
                $pluck_services = $this->category->pluck('services');
                foreach ($pluck_services as $service) {
                    foreach ($service as $key => $value) {
                        $services->push($value);
                    }
                }
            } else {
                $this->category = $this->loadServices($this->category);
                $services = $this->category->services;
            }
            $this->getStartPrice($services);
        } catch (\Throwable $e) {
            $this->price = null;
        }
    }

    private function isMasterCategory()
    {
        return $this->category->parent_id == null;
    }

    private function loadServices($categories)
    {
        return $categories->load(['services' => function ($q) {
            $q->published()->with(['partnerServices' => function ($q) {
                $q->published()->with(['discounts' => function ($q) {
                    $now = Carbon::now();
                    $q->where(function ($query) use ($now) {
                        $query->where('start_date', '<=', $now);
                        $query->where('end_date', '>=', $now);
                    });
                }, 'partner' => function ($q) {
                    $q->published()->with('walletSetting')->whereHas('locations', function ($query) {
                        $query->where('id', $this->location_id);
                    });
                }]);
            }]);
        }]);
    }

    private function getStartPrice($services)
    {
        $service_prices = collect();
        foreach ($services as $service) {
            foreach ($service->partnerServices as $partnerService) {
                /** @var Partner $partner */
                $partner = $partnerService->partner;
                if ($partnerService->partner) {
                    if ($partner->hasAppropriateCreditLimit()) {
                        $prices = $partnerService->prices;
                        if ($service->isFixed()) {
                            $price = (float)$prices;
                        } else {
                            $prices = (array)json_decode($prices);
                            $price = (float)min($prices);
                        }
                        $service_prices->push($price);
                    }
                }
            }
        }
        $this->price = $service_prices->min();
    }
}