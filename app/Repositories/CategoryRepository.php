<?php namespace App\Repositories;

use App\Models\Service;

class CategoryRepository
{
    private $serviceRepository;
    private $reviewRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function getServicesOfCategory($category_ids, $location, $offset, $limit)
    {
        $services = Service::with(['partnerServices' => function ($q) use ($location) {
            $q->where([['is_published', 1], ['is_verified', 1]])->with(['partner' => function ($q) use ($location) {
                $q->where('status', 'Verified')->whereHas('locations', function ($query) use ($location) {
                    $query->where('id', $location);
                });
            }]);
        }])->whereHas('locations',function($q) use ($location) {
            $q->where('locations.id', $location);
        })->select('id', 'category_id', 'name', 'bn_name', 'thumb', 'banner', 'app_thumb', 'app_banner', 'slug', 'min_quantity', 'short_description', 'description', 'variable_type', 'variables', 'faqs')
            ->whereIn('category_id', $category_ids)->skip($offset)->take($limit);

        if (request()->get('is_for_backend')) {
            $services = $services->publishedForAll()->get();
        } else {
            $services = (int)request()->get('is_business') ? $services->publishedForBusiness()->get() : $services->published()->get();
        }

        $final_services = [];
        foreach ($services as $service) {
            array_push($final_services, $service);
        }

        return $final_services;
    }
}