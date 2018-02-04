<?php

namespace App\Sheba\Queries\Category;

use App\Models\Category;
use App\Models\Location;
use App\Models\PartnerService;
use App\Models\PartnerServiceDiscount;
use App\Repositories\PartnerRepository;
use App\Sheba\Checkout\Discount;
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
                foreach ($this->category->pluck('services') as $service) {
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
            $q->published()->with(['partners' => function ($q) {
                $q->with('walletSetting')->where([
                    ['partners.status', 'Verified'],
                    ['is_verified', 1],
                    ['is_published', 1]
                ])->whereHas('locations', function ($query) {
                    $query->where('id', $this->location_id);
                });
            }]);
        }]);
    }

    private function getStartPrice($services)
    {
        $service_prices = collect();
        foreach ($services as $service) {
            foreach ($service->partners as $partner) {
                if ((new PartnerRepository($partner))->hasAppropriateCreditLimit()) {
                    $prices = $partner->pivot->prices;
                    if ($service->isFixed()) {
                        $price = (float)$prices;
                    } else {
                        $prices = (array)json_decode($prices);
                        $price = (float)min($prices);
                    }
                    $discount = new Discount($price, $service->min_quantity);
                    $discount->calculateServiceDiscount((PartnerService::find($partner->pivot->id))->discount());
                    $service_prices->push($discount->__get('discounted_price'));
                }
            }
        }
        $this->price = $service_prices->min();
    }
}