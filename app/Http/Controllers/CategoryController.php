<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use App\Sheba\Queries\Category\StartPrice;
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
        try {
            $category_ids = [183, 185, 184, 1, 5, 73, 186, 3];
            $categories = [];
            $location = $request->location;
            foreach ($category_ids as $category_id) {
                $category = Category::where('id', $category_id)->select('id', 'name', 'thumb', 'banner', 'parent_id')->first();
                if ($request->has('with')) {
                    $with = $request->with;
                    if ($with == 'children') {
                        $category->children->each(function (&$child) use ($location) {
                            $start_price = new StartPrice($child, $location);
                            $start_price->calculate();
                            $child['starting_price'] = $start_price->price;
                            removeRelationsAndFields($child);
                        });
                    }
                }
                array_add($category, 'slug', str_slug($category->name, '-'));
                array_push($categories, $category);
            }
            return count($categories) > 0 ? api_response($request, $categories, 200, ['categories' => $categories]) : api_response($request, $categories, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function show($category, Request $request)
    {
        try {
            $category = Category::select('id', 'name', 'short_description', 'long_description', 'thumb', 'banner', 'app_thumb', 'app_banner', 'publication_status', 'icon', 'questions')->published()->where('id', $category)->first();
            if ($category == null) {
                return api_response($request, null, 404);
            }
            $category->load(['partners' => function ($q) {
                $q->verified();
            }, 'services' => function ($q) {
                $q->published();
            }, 'partnerResources' => function ($q) {
                $q->whereHas('resource', function ($query) {
                    $query->verified();
                });
            }]);
            array_add($category, 'total_partners', $category->partners->count());
            array_add($category, 'total_experts', $category->partnerResources->count());
            array_add($category, 'total_services', $category->services->count());
            removeRelationsAndFields($category);
            return api_response($request, $category, 200, ['category' => $category]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getSecondaries($category, Request $request)
    {
        try {
            $category = Category::find($category);
            $location = $request->location;
            $children = $category->children;
            if (count($children) != 0) {
                $children = $children->each(function (&$child) use ($location) {
                    $start_price = new StartPrice($child, $location);
                    $start_price->calculate();
                    $child['starting_price'] = $start_price->price;
                    removeRelationsAndFields($child);
                });
                $category = collect($category)->only(['name', 'banner']);
                $category->put('secondaries', $children);
                return api_response($request, $category->all(), 200, ['category' => $category->all()]);
            } else
                return api_response($request, null, 404);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getMaster($category)
    {
        $category = Category::find($category);
        $parent = $category->parent()->select('id', 'name', 'thumb', 'banner')->first();
        if ($parent)
            return response()->json(['parent' => $parent, 'msg' => 'successful', 'code' => 200]);
        return response()->json(['msg' => 'not found', 'code' => 404]);
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

    public function getReviews($category, Request $request)
    {
        try {
            $category = Category::find($category);
            $category->load(['reviews' => function ($q) {
                $q->select('id', 'category_id', 'customer_id', 'rating', 'review', 'review_title')->notEmptyReview()->whereIn('rating', [4, 5])->with(['customer' => function ($q) {
                    $q->with(['profile' => function ($q) {
                        $q->select('id', 'name', 'pro_pic');
                    }]);
                }]);
            }]);
            $reviews = $category->reviews;
            foreach ($reviews as $review) {
                $review['customer_name'] = $review->customer ? $review->customer->profile->name : null;
                $review['customer_picture'] = $review->customer ? $review->customer->profile->pro_pic : null;
                removeRelationsAndFields($review);
            }
            return $reviews;
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
