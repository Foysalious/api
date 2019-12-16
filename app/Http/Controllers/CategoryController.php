<?php namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\CategoryGroupCategory;
use App\Models\CategoryPartner;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\LocationService;
use App\Models\Partner;
use App\Models\ReviewQuestionAnswer;
use App\Models\Service;
use App\Models\ServiceGroupService;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use Dingo\Api\Routing\Helpers;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Sheba\CategoryServiceGroup;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountRules;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Location\Coords;
use Sheba\LocationService\PriceCalculation;
use Sheba\ModificationFields;
use Sheba\OrderPlace\DeliveryChargeCalculator;
use Throwable;

class CategoryController extends Controller
{
    use Helpers, ModificationFields, CategoryServiceGroup;

    private $categoryRepository;
    private $serviceRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
        $this->serviceRepository = new ServiceRepository();
    }

    public function index(Request $request)
    {
        $is_business = $request->has('is_business') && (int)$request->is_business;
        $is_partner = ($request->has('is_partner') && (int)$request->is_partner) || in_array($request->header('portal-name'), ['manager-app', 'bondhu-app']);
        $is_b2b = $request->has('is_b2b') && (int)$request->is_b2b;
        $is_partner_registration = $request->has('is_partner_registration') && (int)$request->is_partner_registration;

        $filter_publication = function ($q) use ($request, $is_business, $is_partner, $is_b2b, $is_partner_registration) {
            if ($is_business) {
                $q->publishedForBusiness();
            } elseif ($is_partner) {
                $q->publishedForPartner();
            } elseif ($is_partner_registration) {
                $q->publishedForPartnerOnboarding();
            } elseif ($is_b2b) {
                $q->publishedForB2b();
            } else {
                $q->published();
            }
        };
        try {
            $this->validate($request, ['location' => 'sometimes|numeric', 'lat' => 'sometimes|numeric', 'lng' => 'required_with:lat']);

            $with = '';
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location;
            }
            $best_deal_categories_id = explode(',', config('sheba.best_deal_ids'));
            $best_deal_category = CategoryGroupCategory::whereIn('category_group_id', $best_deal_categories_id)->pluck('category_id')->toArray();

            $categories = Category::where('parent_id', null);
            if ($is_b2b) $categories = $categories->orderBy('order_for_b2b');
            else $categories = $categories->orderBy('order');

            if ($location) {
                $categories = $categories->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location->id);
                });
                $categories = $categories->whereHas('allChildren', function ($q) use ($location, $request, $filter_publication) {
                    $filter_publication($q);
                    $q->whereHas('locations', function ($query) use ($location) {
                        $query->where('locations.id', $location->id);
                    });
                });
            }
            $categories = $categories->select('id', 'name', 'bn_name', 'slug', 'thumb', 'banner', 'icon_png', 'icon', 'order', 'parent_id');
            if ($request->has('with')) {
                $with = $request->with;
                if ($with == 'children') {
                    $categories->with(['allChildren' => function ($q) use ($location, $filter_publication, $best_deal_category, $is_business, $is_b2b) {
                        if (!is_null($location)) {
                            $q->whereHas('locations', function ($q) use ($location) {
                                $q->where('locations.id', $location->id);
                            });
                            $q->whereHas('services', function ($q) use ($location, $is_business, $is_b2b) {
                                if ($is_business) {
                                    $q->publishedForBusiness();
                                } elseif ($is_b2b) {
                                    $q->publishedForB2b();
                                } else {
                                    $q->published();
                                }
                                $q->whereHas('locations', function ($q) use ($location) {
                                    $q->where('locations.id', $location->id);
                                });
                            });
                        }
                        $q->whereNotIn('id', $best_deal_category);
                        $filter_publication($q);
                        if ($is_business) $q->orderBy('order_for_b2b');
                        else $q->orderBy('order');
                    }]);
                }
            }

            $filter_publication($categories);
            //$categories = $request->has('is_business') && (int)$request->is_business ? $categories->publishedForBusiness() : $categories->published();
            $categories = $categories->get();

            foreach ($categories as $key => &$category) {
                if ($with == 'children') {
                    $category->children = $category->allChildren;
                    unset($category->allChildren);
                    if ($category->children->isEmpty()) {
                        $categories->forget($key);
                        continue;
                    }
                    $category->children->sortBy('order')->each(function (&$child) {
                        removeRelationsAndFields($child);
                    });
                }
            }

            $categories_final = array();
            foreach ($categories as $category) {
                array_push($categories_final, $category);
            }
            return count($categories) > 0 ? api_response($request, $categories, 200, ['categories' => $categories_final]) : api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAllCategories(Request $request)
    {
        try {
            $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric', 'with' => 'sometimes|string|in:children']);
            $with = $request->with;
            $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->first();
            if (!$hyper_location) return api_response($request, null, 404);
            $location_id = $hyper_location->location_id;
            $best_deal_category_group_id = explode(',', config('sheba.best_deal_ids'));
            $best_deal_category_ids = CategoryGroupCategory::select('category_group_id', 'category_id')
                ->whereIn('category_group_id', $best_deal_category_group_id)->pluck('category_id')->toArray();

            $categories = Category::published()
                ->whereHas('locations', function ($q) use ($location_id) {
                    $q->select('locations.id')->where('locations.id', $location_id);
                })
                ->whereHas('children', function ($q) use ($location_id, $best_deal_category_ids) {
                    $q->select('id', 'parent_id')->where('publication_status', 1)
                        ->whereHas('locations', function ($q) use ($location_id) {
                            $q->select('locations.id')->where('locations.id', $location_id);
                        })->whereHas('services', function ($q) use ($location_id) {
                            $q->select('services.id')->published()->whereHas('locations', function ($q) use ($location_id) {
                                $q->select('locations.id')->where('locations.id', $location_id);
                            });
                        })->whereNotIn('id', $best_deal_category_ids);
                })
                ->select('id', 'name', 'parent_id', 'icon_png', 'app_thumb', 'app_banner', 'slug')
                ->parent()->orderBy('order');

            if ($with) {
                $categories->with(['children' => function ($q) use ($location_id, $best_deal_category_ids) {
                    $q->select('id', 'name', 'thumb', 'parent_id', 'app_thumb', 'icon_png', 'icon', 'slug')
                        ->whereHas('locations', function ($q) use ($location_id) {
                            $q->select('locations.id')->where('locations.id', $location_id);
                        })->whereHas('services', function ($q) use ($location_id) {
                            $q->select('services.id')->published()->whereHas('locations', function ($q) use ($location_id) {
                                $q->select('locations.id')->where('locations.id', $location_id);
                            });
                        })->whereNotIn('id', $best_deal_category_ids)
                        ->published()->orderBy('order');
                }]);
            }

            $categories = $categories->get();

            foreach ($categories as &$category) {
                array_forget($category, 'parent_id');
                foreach ($category->children as &$child) {
                    array_forget($child, 'parent_id');
                }
            }

            foreach ($categories as &$category) {
                if (is_null($category->children)) app('sentry')->captureException(new Exception('Category null on ' . $category->id));
            }
            return count($categories) > 0 ? api_response($request, $categories, 200, ['categories' => $categories]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getSecondaries($category, Request $request)
    {
        try {
            $this->validate($request, ['location' => 'sometimes|numeric', 'lat' => 'sometimes|numeric', 'lng' => 'required_with:lat']);
            $location = null;
            $category = Category::find($category);
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation)) $location = $hyperLocation->location;
            }

            $best_deal_categories_id = explode(',', config('sheba.best_deal_ids'));
            $best_deal_category = CategoryGroupCategory::whereIn('category_group_id', $best_deal_categories_id)->pluck('category_id')->toArray();
            $category->load(['children' => function ($q) use ($best_deal_category, $location) {
                $q->published()->whereNotIn('id', $best_deal_category);
                if ($location) {
                    $q->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location->id);
                    });
                }
                $q->whereHas('services', function ($q) use ($location) {
                    $q->published();
                    if ($location) {
                        $q->whereHas('locations', function ($q) use ($location) {
                            $q->where('locations.id', $location->id);
                        });
                    }
                });
            }]);
            $children = $category->children->filter(function ($sub_category) use ($best_deal_category) {
                return !in_array($sub_category->id, $best_deal_category);
            });

            if (count($children) != 0) {
                $children = $children->each(function (&$child) use ($location) {
                    removeRelationsAndFields($child);
                });
                $category = collect($category)->only(['name', 'banner', 'app_banner']);
                $category->put('secondaries', $children->sortBy('order')->values()->all());
                return api_response($request, $category->all(), 200, ['category' => $category->all()]);
            } else
                return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getMaster($category)
    {
        $category = Category::find($category);
        $parent = $category->parent()->select('id', 'name', 'thumb', 'banner')->first();
        if ($parent) return response()->json(['parent' => $parent, 'msg' => 'successful', 'code' => 200]);
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $category
     * @param Request $request
     * @param PriceCalculation $price_calculation
     * @param DeliveryCharge $delivery_charge
     * @param JobDiscountHandler $job_discount_handler
     * @return JsonResponse
     */
    public function getServices($category, Request $request, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler)
    {
        ini_set('memory_limit', '2048M');
        try {
            $subscription_faq = null;
            if ($request->has('location')) {
                $location = $request->location != '' ? $request->location : 4;
            } else {
                if ($request->has('lat') && $request->has('lng')) {
                    $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                    if (!is_null($hyperLocation)) $location = $hyperLocation->location->id; else return api_response($request, null, 404);
                } else $location = 4;
            }

            /** @var Category $cat */
            $cat = Category::where('id', $category)->whereHas('locations', function ($q) use ($location) {
                $q->where('locations.id', $location);
            });

            if ((int)$request->is_business) {
                $category = $cat->publishedForBusiness()->first();
            } elseif ((int)$request->is_b2b) {
                $category = $cat->publishedForB2B()->first();
            } else {
                $category = $cat->published()->first();
            }

            if ($category != null) {
                list($offset, $limit) = calculatePagination($request);
                $scope = [];
                if ($request->has('scope')) $scope = $this->serviceRepository->getServiceScope($request->scope);

                if ($category->parent_id == null) {
                    if ((int)$request->is_business) {
                        $services = $this->categoryRepository->getServicesOfCategory((Category::where('parent_id', $category->id)->publishedForBusiness()->orderBy('order')->get())->pluck('id')->toArray(), $location, $offset, $limit);
                    } else if ($request->is_b2b) {
                        $services = $this->categoryRepository->getServicesOfCategory(Category::where('parent_id', $category->id)->publishedForB2B()
                            ->orderBy('order')->get()->pluck('id')->toArray(), $location, $offset, $limit);
                    } else {
                        $services = $this->categoryRepository->getServicesOfCategory($category->children->sortBy('order')->pluck('id'), $location, $offset, $limit);
                    }
                    $services = $this->serviceRepository->addServiceInfo($services, $scope);
                } else {
                    $category = $category->load(['services' => function ($q) use ($offset, $limit, $location) {
                        if (!(int)\request()->is_business) {
                            $q->whereNotIn('id', $this->serviceGroupServiceIds())
                                ->whereHas('locations', function ($query) use ($location) {
                                    $query->where('locations.id', $location);
                                });
                        }
                        $q->select(
                            'id', 'category_id', 'unit', 'name', 'bn_name', 'thumb',
                            'app_thumb', 'app_banner', 'short_description', 'description',
                            'banner', 'faqs', 'variables', 'variable_type', 'min_quantity'
                        )->orderBy('order')->skip($offset)->take($limit);

                        if ((int)\request()->is_business) $q->publishedForBusiness();
                        elseif ((int)\request()->is_for_backend) $q->publishedForAll();
                        elseif ((int)\request()->is_b2b) $q->publishedForB2B();
                        else $q->published();
                    }]);

                    $services = $this->serviceRepository->getPartnerServicesAndPartners($category->services, $location)->each(function ($service) use ($request) {
                        $service->partners = $service->partners->filter(function (Partner $partner) use ($request) {
                            return $partner->hasCoverageOn(new Coords((double)$request->lat, (double)$request->lng));
                        });
                        list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                        removeRelationsAndFields($service);
                    });
                }

                if ($location) {
                    $services->load(['activeSubscription', 'locations' => function ($q) {
                        $q->select('locations.id');
                    }]);

                    $services = collect($services);
                    $services = $services->filter(function ($service) use ($location) {
                        $locations = $service->locations->pluck('id')->toArray();
                        return in_array($location, $locations);
                    });
                }

                if ($request->has('service_id')) {
                    $services = $services->filter(function ($service) use ($request) {
                        return $request->service_id == $service->id;
                    });
                }

                $subscriptions = collect();
                $services->each(function (&$service) use ($price_calculation, $location) {
                    /** @var LocationService $location_service */
                    $location_service = LocationService::where('location_id', $location)->where('service_id', $service->id)->first();
                    /** @var ServiceDiscount $discount */
                    $discount = $location_service->discounts()->running()->first();
                    $prices = json_decode($location_service->prices);
                    $price_calculation->setLocationService($location_service);
                    if ($service->variable_type == 'Options') {
                        $service['option_prices'] = $this->formatOptionWithPrice($price_calculation, $prices);
                    } else {
                        $service['fixed_price'] = $price_calculation->getUnitPrice();
                    }
                    $service['discount'] = $discount ? [
                        'value' => (double)$discount->amount,
                        'is_percentage' => $discount->isPercentage(),
                        'cap' => (double)$discount->cap
                    ] : null;
                });
                foreach ($services as $service) {
                    if ($subscription = $service->activeSubscription) {
                        list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                        $subscription->min_price = $service->min_price;
                        $subscription->max_price = $service->max_price;
                        $subscription['thumb'] = $service['thumb'];
                        $subscription['banner'] = $service['banner'];
                        $subscription['offers'] = $subscription->getDiscountOffers();
                        if ($subscription->faq) {
                            $faq = json_decode($subscription->faq);
                            if ($faq->title && $faq->description) {
                                $subscription_faq = [
                                    'title' => $faq->title,
                                    'body' => $faq->description,
                                    'image' => $faq->image_link ? $faq->image_link : "https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/categories_images/thumbs/1564579810_subscription_image_link.png",
                                ];
                            }
                        }
                        $subscriptions->push($subscription);
                    }
                    removeRelationsAndFields($service);
                }

                if ($services->count() > 0) {
                    $category = collect($category)->only(['name', 'slug', 'banner', 'parent_id', 'app_banner']);
                    $version_code = (int)$request->header('Version-Code');
                    $services = $this->serviceQuestionSet($services);
                    if ($version_code && $version_code <= 30122 && $version_code <= 107) {
                        $services = $services->reject(function ($service) use ($version_code) {
                            return $service->subscription;
                        })->values()->all();
                    }
                    $category['services'] = $services;
                    $category['subscriptions'] = $subscriptions;

                    $category['delivery_charge'] = $delivery_charge->setCategory($service->category)->get();
                    $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($category['delivery_charge']);
                    $job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($service->category)->setCheckingParams($discount_checking_params)->calculate();
                    /** @var Discount $delivery_discount */
                    $delivery_discount = $job_discount_handler->getDiscount();
                    $category['delivery_discount'] = $delivery_discount ? [
                        'value' => (double)$delivery_discount->amount,
                        'is_percentage' => $delivery_discount->is_percentage,
                        'cap' => (double)$delivery_discount->cap,
                        'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
                    ] : null;

                    if ($subscriptions->count()) {
                        $category['subscription_faq'] = $subscription_faq;
                    }
                    return api_response($request, $category, 200, ['category' => $category]);
                } else
                    return api_response($request, null, 404);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param PriceCalculation $price_calculation
     * @param $prices
     * @return Collection
     */
    private function formatOptionWithPrice(PriceCalculation $price_calculation, $prices)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $option_array = explode(',', $key);
            $options->push(array('option' => collect($option_array)->map(function ($key) {
                return (int)$key;
            }), 'price' => $price_calculation->setOption($option_array)->getUnitPrice()));
        }
        return $options;
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
        } catch (Throwable $e) {
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
            $reviews = ReviewQuestionAnswer::select('category_id', 'customer_id', 'partner_id', 'reviews.rating', 'review_title')
                ->selectRaw("partners.name as partner_name,profiles.name as customer_name,rate_answer_text as review,review_id as id,pro_pic as customer_picture")
                ->join('reviews', 'reviews.id', '=', 'review_question_answer.review_id')
                ->join('partners', 'partners.id', '=', 'reviews.partner_id')
                ->join('customers', 'customers.id', '=', 'reviews.customer_id')
                ->join('profiles', 'profiles.id', '=', 'customers.profile_id')
                ->where('review_type', 'like', '%' . '\\Review')
                ->where('review_question_answer.rate_answer_text', '<>', '')
                ->whereIn('reviews.rating', [4, 5])
                ->where('reviews.category_id', $category->id)
                ->skip($offset)->take($limit)
                ->orderBy('id', 'desc')
                ->groupBy('customer_id')
                ->get();
            $group_rating = [
                [
                    "value" => 1,
                    "count" => 10
                ],
                [
                    "value" => 2,
                    "count" => 2
                ],
                [
                    "value" => 3,
                    "count" => 15
                ],
                [
                    "value" => 4,
                    "count" => 150
                ],
                [
                    "value" => 5,
                    "count" => 500
                ]];
            return count($reviews) > 0 ? api_response($request, $reviews, 200, ['reviews' => $reviews, 'group_rating' => $group_rating]) : api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function addCategories(Request $request)
    {
        try {
            $this->validate($request, ['categories' => "required|string"]);
            $partner = $request->partner;
            $manager_resource = $request->manager_resource;
            $this->setModifier($manager_resource);
            $categories = explode(',', $request->categories);
            $partner_categories = CategoryPartner::where('partner_id', $partner->id)->whereIn('category_id', $categories)->get();
            $category_partners = [];
            foreach ($categories as $category) {
                $has_category_partner = $partner_categories->where('category_id', (int)$category)->first();
                if (!$has_category_partner) {
                    array_push($category_partners, $this->withCreateModificationField([
                        'response_time_min' => 60,
                        'response_time_max' => 120,
                        'commission' => $partner->commission,
                        'category_id' => $category,
                        'partner_id' => $partner->id
                    ]));
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerLocationCategory(Request $request, $partner)
    {
        try {
            $geo_info = json_decode($request->partner->geo_informations);
            $hyper_locations = HyperLocal::insideCircle($geo_info)->with('location')->get()->filter(function ($item) {
                return !empty($item->location);
            })->pluck('location')->pluck('id');
            $category = Category::locationWise($hyper_locations)->get();
            $category = $category->filter(function ($item) {
                return $item->children->count() > 0;
            });
            if ($category->count() > 0) {
                return api_response($request, $request, 200, ['data' => ['categories' => $category]]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }
}
