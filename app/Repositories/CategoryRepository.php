<?php

namespace App\Repositories;


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

    public function getServicesOfCategory($category_ids, $offset, $limit)
    {
        $services = Service::with(['partnerServices' => function ($q) {
            $q->where([['is_published', 1], ['is_verified', 1]])->with(['partner' => function ($q) {
                $q->where('status', 'Verified');
            }]);
        }])->select('id', 'category_id', 'name', 'bn_name', 'thumb', 'banner', 'app_thumb', 'app_banner', 'slug', 'min_quantity', 'short_description', 'description', 'variable_type', 'variables', 'faqs')
            ->whereIn('category_id', $category_ids)->skip($offset)->take($limit);
        $services = (int)request()->get('is_business') ? $services->publishedForBusiness()->get() : $services->published()->get();
        $final_services = [];
        foreach ($services as $service) {
            array_push($final_services, $service);
        }
        return $final_services;
    }

}