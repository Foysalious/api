<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryPartner;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\Service;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $with = '';
            $location = null;
            if($request->has('location') ) {
                $location = Location::find($request->location);
            } else if($request->has('lat')) {
                $hyperLocation= HyperLocal::insidePolygon((double) $request->lat, (double)$request->lng)->with('location')->first();
                if(!is_null($hyperLocation)) $location = $hyperLocation->location;
            }
            $categories = Category::where('parent_id', null)->orderBy('order');
            if($location){
                $categories= $categories ->whereHas('locations',function($q) use ($location) {
                    $q->where('locations.id', $location->id);
                });
            }
            $categories= $categories->select('id', 'name', 'bn_name', 'slug', 'thumb', 'banner', 'icon_png', 'icon', 'order', 'parent_id');
            if ($request->has('with')) {
                $with = $request->with;
                if ($with == 'children') {
                    $categories->with(['children' => function ($q) use($location) {
                        if(!is_null($location)) {
                            $q->whereHas('locations' , function($q) use ($location) {
                                $q->where('locations.id', $location->id);
                            });
                        }
                        $q->orderBy('order');
                    }]);
                }
            }
            $categories = $request->has('is_business') && (int)$request->is_business ? $categories->publishedForBusiness() : $categories->published();
            $categories = $categories->get();

            foreach ($categories as &$category) {
                if ($with == 'children') {
                    $category->children->sortBy('order')->each(function (&$child) {
                        removeRelationsAndFields($child);
                    });
                }
            }
            return count($categories) > 0 ? api_response($request, $categories, 200, ['categories' => $categories]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($category, Request $request)
    {
        try {
            $category = Category::select('id', 'name', 'short_description', 'long_description', 'thumb', 'video_link', 'banner', 'app_thumb', 'app_banner', 'publication_status', 'icon', 'questions')->published()->where('id', $category)->first();
            if ($category == null) {
                return api_response($request, null, 404);
            }
            $category->load(['partners' => function ($q) {
                $q->verified();
            }, 'services' => function ($q) {
                $q->published();
            }, 'usps' => function ($q) {
                $q->select('usps.id', 'name', 'category_usp.value');
            }, 'partnerResources' => function ($q) {
                $q->whereHas('resource', function ($query) {
                    $query->verified();
                });
            }]);
            array_add($category, 'total_partners', $category->partners->count());
            array_add($category, 'total_experts', $category->partnerResources->count());
            array_add($category, 'total_services', $category->services->count());
            array_add($category, 'selling_points', $category->usps->each(function ($usp) {
                removeRelationsAndFields($usp);
            }));
            removeRelationsAndFields($category);
            return api_response($request, $category, 200, ['category' => $category]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getSecondaries($category, Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $location = null;
            $category = Category::find($category);
            if($request->has('location') ) {
                $location = Location::find($request->location);
            } else if($request->has('lat')) {
                $hyperLocation= HyperLocal::insidePolygon((double) $request->lat, (double)$request->lng)->with('location')->first();
                if(!is_null($hyperLocation)) $location = $hyperLocation->location;
            }
            if($location) {
                $children = $category->children->filter(function($secondary) use($location) {
                    $locations = $secondary->locations()->pluck('id')->toArray();
                    return in_array($location->id, $locations);
                });
            }
            else
                $children = $category->children;
            if (count($children) != 0) {
                $children = $children->each(function (&$child) use ($location) {
                    removeRelationsAndFields($child);
                });
                $category = collect($category)->only(['name', 'banner', 'app_banner']);
                $category->put('secondaries', $children->sortBy('order')->values()->all());
                return api_response($request, $category->all(), 200, ['category' => $category->all()]);
            } else
                return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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

    public function getPartnersOfLocation($category, $location, Request $request)
    {
        try {
            $category = Category::find($category);
            $category->load(['partners' => function ($q) use ($location) {
                $q->verified()->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', (int)$location);
                });
            }]);
            $available_partners = $category->partners;
            $total_available_partners = count($available_partners);
            return api_response($request, $available_partners, 200, ['total_available_partners' => $total_available_partners, 'isAvailable' => $total_available_partners > 0 ? 1 : 0]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getServices($category, Request $request)
    {
        try {
            $category = Category::where('id', $category);
            $category = ((int)$request->is_business ? $category->publishedForBusiness() : $category->published())->first();
            if ($category != null) {
                list($offset, $limit) = calculatePagination($request);
                if($request->has('location')) {
                    $location = $request->location != '' ? $request->location : 4;
                } else {
                    if($request->has('lat') && $request->has('lng')) {
                        $hyperLocation= HyperLocal::insidePolygon((double) $request->lat, (double)$request->lng)->with('location')->first();
                        if(!is_null($hyperLocation)) $location = $hyperLocation->location->id;
                        else return api_response($request, null, 404);
                    }
                    else $location = 4;
                }

                $scope = [];
                if ($request->has('scope')) $scope = $this->serviceRepository->getServiceScope($request->scope);
                if ($category->parent_id == null) {
                    if ((int)$request->is_business) {
                        $services = $this->categoryRepository->getServicesOfCategory((Category::where('parent_id', $category->id)->publishedForBusiness()->orderBy('order')->get())->pluck('id')->toArray(), $location, $offset, $limit);
                    } else {
                        $services = $this->categoryRepository->getServicesOfCategory($category->children->sortBy('order')->pluck('id'), $location, $offset, $limit);
                    }
                    $services = $this->serviceRepository->addServiceInfo($services, $scope);
                } else {
                    $category = $category->load(['services' => function ($q) use ($offset, $limit, $location) {
                        $q->whereHas('locations', function ($query) use ($location) {
                            $query->where('locations.id', $location);
                        });
                        $q->select('id', 'category_id', 'unit', 'name', 'bn_name', 'thumb', 'app_thumb', 'app_banner',
                            'short_description', 'description', 'banner', 'faqs', 'variables', 'variable_type', 'min_quantity')->orderBy('order')->skip($offset)->take($limit);
                        if ((int)\request()->is_business) $q->publishedForBusiness();
                        else $q->published();
                    }]);
                    $services = $this->serviceRepository->getPartnerServicesAndPartners($category->services, $location)->each(function ($service) {
                        list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                        removeRelationsAndFields($service);
                    });
                }
                $category = collect($category)->only(['name', 'banner', 'parent_id', 'app_banner']);
                $category['services'] = $this->serviceQuestionSet($services);
                return api_response($request, null, 200, ['category' => $category]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getPriceRange(Service $service)
    {
        try {
            $max_price = [];
            $min_price = [];
            if ($service->partners->count() == 0) return array(0, 0);
            foreach ($service->partners->where('status', 'Verified') as $partner) {
                $partner_service = $partner->pivot;
                if (!($partner_service->is_verified && $partner_service->is_published)) continue;
                $prices = (array)json_decode($partner_service->prices);
                $max = max($prices);
                $min = min($prices);
                array_push($max_price, $max);
                array_push($min_price, $min);
            }
            return array((double)max($max_price) * $service->min_quantity, (double)min($min_price) * $service->min_quantity);
        } catch (\Throwable $e) {
            return array(0, 0);
        }
    }

    private function serviceQuestionSet($services)
    {
        foreach ($services as &$service) {
            $questions = null;
            $service['type'] = 'normal';
            if ($service->variable_type == 'Options') {
                $questions = json_decode($service->variables)->options;
                foreach ($questions as &$question) {
                    $question = collect($question);
                    $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                    $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                    $explode_answers = explode(',', $question->get('answers'));
                    $question->put('answers', $explode_answers);
                }
                if (count($questions) == 1) {
                    $questions[0]->put('input_type', 'selectbox');
                }
            }
            $service['questions'] = $questions;
            $service['faqs'] = json_decode($service->faqs);
            array_forget($service, 'variables');
        }
        return $services;
    }

    private function resolveInputTypeField($answers)
    {
        $answers = explode(',', $answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }

    private function resolveScreenField($question)
    {
        $words = explode(' ', trim($question));
        return count($words) <= 5 ? "normal" : "slide";
    }

    public function getReviews($category, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $category = Category::find($category);
            if (!$category) return api_response($request, null, 404);
            $category->load(['reviews' => function ($q) use ($offset, $limit) {
                $q->select('id', 'category_id', 'customer_id', 'rating', 'review', 'review_title', 'partner_id')->whereIn('rating', [4, 5])->orderBy('created_at', 'desc')->with(['rates', 'customer.profile', 'partner']);
            }]);
            $reviews = $category->reviews->each(function ($review) {
                $review->review = $review->calculated_review;
                $review['customer_name'] = $review->customer ? $review->customer->profile->name : null;
                $review['customer_picture'] = $review->customer ? $review->customer->profile->pro_pic : null;
                $review['partner_name'] = $review->partner->name;
                removeRelationsAndFields($review);
            })->filter(function ($review) {
                return (!empty($review->review) && $review->rating == 5);
            })->unique('customer_id')->sortByDesc('id')->splice($offset, $limit)->values()->all();
            return count($reviews) > 0 ? api_response($request, $reviews, 200, ['reviews' => $reviews]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function addCategories(Request $request)
    {
        try {
            $this->validate($request, [
                'categories' => "required|string"
            ]);
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $by = ["created_by" => $manager_resource->id, "created_by_name" => "Resource - " . $manager_resource->profile->name];
            $categories = explode(',', $request->categories);
            $partner_categories = CategoryPartner::where('partner_id', $partner->id)->whereIn('category_id', $categories)->get();
            $category_partners = [];
            foreach ($categories as $category) {
                $has_category_partner = $partner_categories->where('category_id', (int)$category)->first();
                if (!$has_category_partner) {
                    array_push($category_partners, array_merge([
                        'response_time_min' => 60,
                        'response_time_max' => 120,
                        'commission' => $partner->commission,
                        'category_id' => $category,
                        'partner_id' => $partner->id
                    ], $by));
                }
            }
            CategoryPartner::insert($category_partners);
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
