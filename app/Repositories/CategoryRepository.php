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
    public function childrenWithServices($category, $request)
    {
        $offset = 0;
        if ($request->get('offset') != '') {
            $offset = $request->get('offset');
        }
        $children = $category->children()->select('id', 'name', 'thumb', 'banner')->skip($offset)->take(2)->get();

        foreach ($children as $child) {
            $services = $child->services()->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables')
                ->where('publication_status', 1)->with(['partnerServices' => function ($q) {
                    $q->select('id', 'partner_id', 'service_id')->with(['discounts' => function ($q) {
                        $q->select('id', 'partner_service_id', 'start_date', 'end_date', 'amount');
                    }]);
                }])->take(4)->get();
            array_add($child, 'services', $services);
            array_add($child, 'slug_child_category', str_slug($child->name, '-'));
            array_add($child, 'children_services', $this->addServiceInfo($services));
            array_forget($child, 'services');
        }
        return $children;
    }

    public function addServiceInfo($services)
    {
        foreach ($services as $key => $service) {
            array_add($service, 'discount', $service->hasDiscounts());
            //Get start & end price for services. Custom services don't have price so omitted
            $service = $this->serviceRepository->getStartPrice($service);
            array_add($service, 'slug_service', str_slug($service->name, '-'));
            // review count of this partner for this service
            $review = $service->reviews()->where('review', '<>', '')->count('review');
            //avg rating of the partner for this service
            $rating = $service->reviews()->where('service_id', $service->id)->avg('rating');
            array_add($service, 'review', $review);
            array_add($service, 'rating', $rating);
            array_forget($service, 'variables');
            array_forget($service, 'partnerServices');
        }
        return $services;
    }

}