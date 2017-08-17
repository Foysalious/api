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

    /**
     * Send children with services for a category
     * @param $category
     * @param $request
     * @return mixed
     */
    public function childrenWithServices($category, $request)
    {
        $offset = 0;
        if ($request->get('skip') != '') {
            $offset = $request->get('skip');
        }
        $location = $request->location != '' ? $request->location : 4;
        $children = $category->children->load(['services' => function ($q) {
            $q->select('id', 'category_id', 'name', 'thumb', 'banner', 'slug', 'variable_type', 'variables', 'min_quantity')->where('publication_status', 1);
        }]);
        $children = $this->childrenHasServices($children, $offset);
        foreach ($children as $child) {
            $services = $child->services->load(['partnerServices' => function ($q) use ($location) {
                $q->select('id', 'partner_id', 'service_id', 'is_published', 'is_verified', 'prices')->where([
                    ['is_published', 1],
                    ['is_verified', 1],
                ])->with(['partner' => function ($q) use ($location) {
                    $q->where('status', 'Verified')->whereHas('locations', function ($query) use ($location) {
                        $query->where('id', $location);
                    });
                }]);
            }])->take(4);
            array_forget($child,'services');
            $child['services']=$services;
            array_add($child, 'slug', str_slug($child->name, '-'));
            $child['services'] = $this->serviceRepository->addServiceInfo($child['services'], $request->location);
            array_forget($child->services,'reviews');
        }
        return $children;
    }

    public function getChildrenServices($category, $request)
    {
        $chlidren_category_id = $category->children->pluck('id');
        $services = Service::select('id', 'category_id', 'name', 'thumb', 'variable_type', 'variables', 'min_quantity')
            ->where('publication_status', 1)
            ->whereIn('category_id', $chlidren_category_id)
            ->get()
            ->random(6);
        $final_service = [];
        foreach ($services as $service) {
            array_push($final_service, $service);
        }
        return $this->serviceRepository->addServiceInfo($final_service, $request->location);
    }

    public function childrenHasServices($children, $offset)
    {
        $final = array();
        foreach ($children as $key => $child) {
            if ($child->services->count() > 0) {
                array_push($final, $child);
            }
        }
        return array_slice($final, $offset, 2);
    }

}