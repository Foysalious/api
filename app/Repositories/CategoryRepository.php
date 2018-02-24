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

//    public function childrenWithServices($category, $request)
//    {
//        $offset = $request->offset != '' ? $request->offset : 0;
//        $limit = $request->limit != '' ? $request->limit : 2;
//        $location = $request->location != '' ? $request->location : 4;
//        $service_limit = $request->service != '' ? $request->services : 4;
//
//        $children = $category->children->load(['services' => function ($q) {
//            $q->select('id', 'category_id', 'name', 'thumb', 'banner', 'slug', 'variable_type', 'variables', 'min_quantity')->published();
//        }]);
//        $children = $this->onlyChildrenWithServices($children, $offset, $limit);
//        foreach ($children as $child) {
//            $child['slug'] = str_slug($child->name, '-');
//            $services = $this->serviceRepository->getPartnerServicesAndPartners($child->services, $location, $service_limit);
//            array_forget($child, 'services');
//            $child['services'] = $services;
//            $child['services'] = $this->serviceRepository->addServiceInfo($child['services']);
//        }
//        return $children;
//    }

    public function getServicesOfCategory($category_ids, $location, $offset, $limit)
    {
        $services = Service::with(['partnerServices' => function ($q) use ($location) {
            $q->where([['is_published', 1], ['is_verified', 1]])->with(['partner' => function ($q) use ($location) {
                $q->where('status', 'Verified')->whereHas('locations', function ($query) use ($location) {
                    $query->where('id', $location);
                });
            }]);
        }])->select('id', 'category_id', 'name', 'thumb', 'slug', 'min_quantity', 'description', 'variable_type', 'variables', 'faqs')
            ->where('publication_status', 1)->whereIn('category_id', $category_ids)->skip($offset)->take($limit)->get();
        $final_services = [];
        foreach ($services as $service) {
            array_push($final_services, $service);
        }
        return $final_services;
    }

//    public function onlyChildrenWithServices($children, $offset, $limit)
//    {
//        $final = array();
//        foreach ($children as $key => $child) {
//            if ($child->services->count() > 0) {
//                array_push($final, $child);
//            }
//        }
//        return array_slice($final, $offset, $limit);
//    }

}