<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ScheduleSlot;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use App\Sheba\Queries\Category\StartPrice;
use Carbon\Carbon;
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
            }, 'usps' => function ($q) {
                $q->select('usps.id', 'name');
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
                    removeRelationsAndFields($child);
                });
                $category = collect($category)->only(['name', 'banner', 'app_banner']);
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

    public function getPartnersOfLocation($category, $location, Request $request)
    {
        try {
            $category = Category::find($category);
            $category->load(['partners' => function ($q) use ($location, $category) {
                $q->verified()->with('handymanResources')->whereHas('locations', function ($query) use ($location) {
                    $query->where('locations.id', (int)$location);
                })->whereHas('categories', function ($query) use ($category) {
                    $query->where('categories.id', $category->id)->where('category_partner.is_verified', 1);
                });
            }]);
            $first = $this->getFirstValidSlot();
            foreach ($category->partners as &$partner) {
                if (!scheduler($partner)->isAvailable((Carbon::today())->format('Y-m-d'), explode('-', $first), $category->id)) {
                    unset($partner);
                }
            }
            $available_partners = $category->partners;
            return api_response($request, $available_partners, 200, ['total_available_partners' => $available_partners->count(), 'isAvailable' => count($available_partners) > 0 ? 1 : 0]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function getFirstValidSlot()
    {
        $slots = ScheduleSlot::all();
        $current_time = Carbon::now();
        foreach ($slots as $slot) {
            $slot_start_time = Carbon::parse($slot->start);
            $time_slot_key = $slot->start . '-' . $slot->end;
            if ($slot_start_time > $current_time) {
                return $time_slot_key;
            }
        }
    }

    public function getServices($category, Request $request)
    {
        try {
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
                        $q->select('id', 'category_id', 'unit', 'name', 'thumb', 'short_description', 'description', 'banner', 'faqs', 'variables', 'variable_type', 'min_quantity')->published()->skip($offset)->take($limit);
                    }])->where('id', $category->id)->published()->first();
                    $services = $this->serviceRepository->addServiceInfo($this->serviceRepository->getPartnerServicesAndPartners($category->services, $location), $scope);
                }
                $category = collect($category)->only(['name', 'banner', 'parent_id', 'app_banner']);
                $category['services'] = $this->serviceQuestionSet($services);
                return api_response($request, null, 200, ['category' => $category]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function serviceQuestionSet($services)
    {
        foreach ($services as &$service) {
            $questions = null;
            $service['type'] = 'normal';
            if ($service->variable_type == 'Options') {
                $questions = json_decode($service->variables)->options;
                $slide = false;
                foreach ($questions as &$question) {
                    $question = collect($question);
                    $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                    $screen = $this->resolveScreenField($question->get('question'));
                    if ($screen == 'slide') {
                        $slide = true;
                    }
                    if ($slide) {
                        $question->put('screen', 'slide');
                    } else {
                        $question->put('screen', $screen);
                    }
                    $explode_answers = explode(',', $question->get('answers'));
                    $question->put('answers', $explode_answers);
                }
                $questions = collect($questions);
                $slide_questions = $questions->filter(function ($question) {
                    return $question['screen'] == 'slide';
                });
                if (count($slide_questions) > 0) {
                    $service['type'] = 'slide';
                } else {
                    $service['type'] = 'normal';
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
            return count($reviews) > 0 ? api_response($request, $category, 200, ['reviews' => $reviews]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
