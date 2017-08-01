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
        $children = $category->children()->select('id', 'name', 'thumb', 'banner')->skip($offset)->take(2)->get();

        foreach ($children as $child) {
            $services = $child->services()->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'variables', 'min_quantity')
                ->where('publication_status', 1)->with(['partnerServices' => function ($q) {
                    $q->select('id', 'partner_id', 'service_id')->with(['discounts' => function ($q) {
                        $q->select('id', 'partner_service_id', 'start_date', 'end_date', 'amount');
                    }]);
                }])->take(4)->get();
            array_add($child, 'slug', str_slug($child->name, '-'));
            $child['services'] = $this->serviceRepository->addServiceInfo($services, $request->location);
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

}