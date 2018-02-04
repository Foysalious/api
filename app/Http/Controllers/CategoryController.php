<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Redis;

class CategoryController extends Controller
{
    use Helpers;
    private $categoryRepository;
    private $serviceRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->serviceRepository = new ServiceRepository();
    }

    public function index(Request $request)
    {
        $categories = Category::parents()->select('id', 'name', 'thumb', 'banner')->get();
        foreach ($categories as $category) {
            if ($request->has('with')) {
                $with = $request->has('with');
                if ($with == 'children') {
                    $category->children;
                }
            }
            array_add($category, 'slug', str_slug($category->name, '-'));
        }
        return count($categories) > 0 ? response()
            ->json(['categories' => $categories, 'msg' => 'successful', 'code' => 200]) : response()->json(['msg' => 'nothing found', 'code' => 404]);
    }

    public function getMaster($category)
    {
        $category = Category::find($category);
        $parent = $category->parent()->select('id', 'name', 'thumb', 'banner')->first();
        if ($parent)
            return response()->json(['parent' => $parent, 'msg' => 'successful', 'code' => 200]);
        return response()->json(['msg' => 'not found', 'code' => 404]);
    }

    public function getSecondaries($category, Request $request)
    {
        try {
            $category = Category::find($category);
            $children = $category->children;
            if (count($category->children) != 0) {
                $category = collect($category)->only(['name', 'banner']);
                $category->put('secondaries', $children);
                return api_response($request, $category->all(), 200, ['category' => $category->all()]);
            } else
                return api_response($request, null, 404);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getSecondaryServices($category, Request $request)
    {
        if ($category = $this->api->get('v1/categories/' . $category . '/secondaries')) {
            try {
                $secondaries = $category['secondaries'];
                list($offset, $limit) = calculatePagination($request);
                $location = $request->location != '' ? $request->location : 4;
//                $service_limit = $request->service_limit != '' ? $request->service_limit : 4;
                $scope = [];
                if ($request->has('scope')) {
                    $scope = $this->serviceRepository->getServiceScope($request->scope);
                }
                $secondaries->load(['services' => function ($q) {
                    $q->select('id', 'category_id', 'name', 'thumb', 'banner', 'slug', 'variable_type', 'variables', 'min_quantity');
                }]);
                $secondaries = $secondaries->splice($offset, $limit)->all();
                $category['secondaries'] = $secondaries;
                if (count($secondaries) != 0) {
                    foreach ($secondaries as $secondary) {
                        $secondary['slug'] = str_slug($secondary->name, '-');
                        $services = $secondary->services;
                        if ($request->has('service_limit')) {
                            $services = $services->take($request->service_limit);
                        }
                        if (in_array('discount', $scope) || in_array('start_price', $scope)) {
                            $services = $this->serviceRepository->getpartnerServicePartnerDiscount($services, $location);
                        }
                        if (in_array('reviews', $scope)) {
                            $services->load('reviews');
                        }
                        array_forget($secondary, 'services');
                        $secondary['services'] = $this->serviceRepository->addServiceInfo($services, $scope);
                    }
                    return api_response($request, $category, 200, ['category' => $category]);
                } else {
                    return api_response($request, null, 404);
                }
            } catch (\Exception $e) {
                return api_response($request, null, 500);
            }
        } else {
            return api_response($request, null, 404);
        }
    }

    public function getServices($category, Request $request)
    {
        $category = Category::where('id', $category)->published()->first();
        if ($category != null) {
            list($offset, $limit) = calculatePagination($request);
            $location = $request->location != '' ? $request->location : 4;
            $scope = [];
            if ($request->has('scope')) {
                $scope = $this->serviceRepository->getServiceScope($request->scope);
            }
            if ($category->parent_id == null) {
                $services = $this->categoryRepository->getServicesOfCategory($category->children->pluck('id'), $location, $offset, $limit);
                $services = $this->serviceRepository->addServiceInfo($services, $scope);
            } else {
                $category = Category::with(['services' => function ($q) use ($offset, $limit) {
                    $q->select('id', 'category_id', 'name', 'thumb', 'banner', 'variable_type', 'min_quantity')->published()->skip($offset)->take($limit);
                }])->where('id', $category->id)->published()->first();
                $services = $this->serviceRepository->addServiceInfo($this->serviceRepository->getPartnerServicesAndPartners($category->services, $location), $scope);
            }
            $category = collect($category)->only(['name', 'banner', 'parent_id']);
            $category['services'] = $services;
            return response()->json(['category' => $category, 'msg' => 'successful', 'code' => 200]);
        } else {
            return response()->json(['msg' => 'category not found', 'code' => 404]);
        }
    }
}
