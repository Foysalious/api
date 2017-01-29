<?php

namespace App\Repositories;


class CategoryRepository
{
    private $serviceRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
    }

    /**
     * Send children with services for a category
     * @param $category
     * @return mixed
     */
    public function childrenWithServices($category)
    {
        $children = $category->children()->select('id', 'name', 'thumb', 'banner')
            ->with(['services' => function ($query) {
                $query->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables');
            }])
            ->get();
        foreach ($children as $child) {
            array_add($child, 'slug_child_category', str_slug($child->name, '-'));
            array_add($child, 'children_services', $this->addServiceInfo($child->services->take(6)));
            array_forget($child, 'services');
        }
        return $children;
    }

    public function addServiceInfo($services)
    {
        foreach ($services as $service) {
            //Get start & end price for services. Custom services don't have price so ommitted
          $service=$this->serviceRepository->getStartEndPrice($service);
            array_add($service, 'slug_service', str_slug($service->name, '-'));
            // review count of this partner for this service
            $review = $service->reviews()->where('review', '<>', '')->count('review');
            //avg rating of the partner for this service
            $rating = $service->reviews()->where('service_id', $service->id)->avg('rating');
            array_add($service, 'review', $review);
            array_add($service, 'rating', $rating);
            array_forget($service, 'variables');
        }
        return $services;
    }

}