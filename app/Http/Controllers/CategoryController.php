<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\CategoryGroupCategory;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use App\Models\HyperLocal;
use App\Models\Location;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use Dingo\Api\Routing\Helpers;
use Exception;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Cache\Category\Review\ReviewCacheRequest;
use Sheba\CategoryServiceGroup;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\Dal\ServiceSubscription\ServiceSubscription;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Dal\UniversalSlug\SluggableType;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Location\Coords;
use Sheba\LocationService\CorruptedPriceStructureException;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\ModificationFields;
use Sheba\Service\MinMaxPrice;
use Sheba\Services\ServiceSubscriptionDiscount;
use Sheba\Subscription\ApproximatePriceCalculator;
use stdClass;
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
        $is_ddn = $request->has('is_ddn') && (int)$request->is_ddn;

        $filter_publication = function ($q) use ($request, $is_business, $is_partner, $is_b2b, $is_partner_registration,$is_ddn) {
            if ($is_business) {
                $q->publishedForBusiness();
            } elseif ($is_partner) {
                $q->publishedForPartner();
            } elseif ($is_partner_registration) {
                $q->publishedForPartnerOnboarding();
            } elseif ($is_b2b) {
                $q->publishedForB2b();
            }elseif($is_ddn){
                $q->publishedForDdn();
            }else {
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
            $categories = $categories->select('id', 'name', 'bn_name', 'slug', 'thumb', 'banner', 'icon_png', 'icon', 'order', 'parent_id', 'is_auto_sp_enabled', 'min_order_amount', 'max_order_amount');
            if ($request->has('with')) {
                $with = $request->with;
                if ($with == 'children') {
                    $categories->with(['allChildren' => function ($q) use ($location, $filter_publication, $best_deal_category, $is_business, $is_b2b, $is_ddn) {
                        if (!is_null($location)) {
                            $q->whereHas('locations', function ($q) use ($location) {
                                $q->where('locations.id', $location->id);
                            });
                            $q->whereHas('services', function ($q) use ($location, $is_business, $is_b2b, $is_ddn) {
                                if ($is_business) {
                                    $q->publishedForBusiness();
                                } elseif ($is_b2b) {
                                    $q->publishedForB2b();
                                } elseif ($is_ddn) {
                                    $q->publishedForDdn();
                                }else {
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
                    foreach ($category->children as $children) {
                        $children->parent_name = $category->name;
                        $children->parent_slug = $category->slug;
                    }
                    unset($category->allChildren);
                    if ($category->children->isEmpty()) {
                        $categories->forget($key);
                        continue;
                    }
                    $category->children->sortBy('order')->each(function (&$child) {
                        removeRelationsAndFields($child);
                    });
                }
                $category['max_order_amount'] = $category['max_order_amount'] ? (double) $category['max_order_amount'] : null;

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
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'with' => 'sometimes|string|in:children'
            ]);
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
                ->select('id', 'name', 'parent_id', 'icon_png', 'icon_png_hover', 'icon_png_active', 'app_thumb', 'app_banner', 'slug', 'is_auto_sp_enabled')
                ->parent()->orderBy('order');

            if ($with) {
                $categories->with(['children' => function ($q) use ($location_id, $best_deal_category_ids) {
                    $q->select('id', 'name', 'thumb', 'parent_id', 'app_thumb', 'icon_png', 'icon_png_hover', 'icon_png_active', 'icon', 'icon_hover', 'slug', 'is_auto_sp_enabled')
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

            $secondary_categories_slug = UniversalSlugModel::where('sluggable_type', SluggableType::SECONDARY_CATEGORY)->pluck('slug', 'sluggable_id')->toArray();
            foreach ($categories as &$category) {
                if (is_null($category->children))
                    app('sentry')->captureException(new Exception('Category null on ' . $category->id));

                /** @var Category $category */
                $category->slug = $category->getSlug();
                array_forget($category, 'parent_id');

                foreach ($category->children as &$child) {
                    /** @var Category $child */
                    $child->slug = array_key_exists($child->id, $secondary_categories_slug) ? $secondary_categories_slug[$child->id] : null;
                    array_forget($child, 'parent_id');
                }
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
            $category = Category::select('id', 'parent_id', 'name', 'short_description', 'is_auto_sp_enabled', 'long_description', 'thumb', 'video_link', 'banner', 'app_thumb', 'app_banner', 'publication_status', 'icon', 'questions')->published()->where('id', $category)->first();
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

            $parent_category = $category->parent;
            $master_category = [];
            if ($parent_category) {
                $master_category = [
                    'id' => $parent_category->id,
                    'name' => $parent_category->name,
                    'slug' => $parent_category->getSlug(),
                ];
            }
            array_add($category, 'master_category', count($master_category) > 0 ? $master_category : null);
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

            /*$best_deal_categories_id = explode(',', config('sheba.best_deal_ids'));
            $best_deal_category = CategoryGroupCategory::whereIn('category_group_id', $best_deal_categories_id)->pluck('category_id')->toArray();*/
            $category->load(['children' => function ($q) use ($location, $request) {
                $q->published();/*->whereNotIn('id', $best_deal_category)*/
                if ($location) {
                    $q->whereHas('locations', function ($q) use ($location) {
                        $q->where('locations.id', $location->id);
                    });
                }
                $q->whereHas('services', function ($q) use ($location, $request) {
                    if ($request->has('portal') && $request->portal == 'admin-portal') $q->publishedForAll();
                    else $q->published();

                    if ($location) {
                        $q->whereHas('locations', function ($q) use ($location) {
                            $q->where('locations.id', $location->id);
                        });
                    }
                });
            }]);
            $children = $category->children;
            /*$children = $category->children->filter(function ($sub_category) use ($best_deal_category) {
                return !in_array($sub_category->id, $best_deal_category);
            });*/

            if (count($children) != 0) {
                $children = $children->each(function (&$child) use ($location) {
                    removeRelationsAndFields($child);
                });
                $category = collect($category)->only(['name', 'banner', 'app_banner', 'is_auto_sp_enabled']);
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

    public function getServices($category, Request $request,
                                PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                                JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation, MinMaxPrice $min_max_price, ApproximatePriceCalculator $approximate_price_calculator, ServiceSubscriptionDiscount $subscriptionDiscount)
    {
        ini_set('memory_limit', '2048M');
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
        } elseif ((int)$request->is_ddn) {
            $category = $cat->publishedForDdn()->first();
        } else {
            $category = $cat->published()->first();
        }


        if ($category != null) {
            $category_slug = $category->getSlug();
            list($offset, $limit) = calculatePagination($request);
            $scope = [];
            if ($request->has('scope')) $scope = $this->serviceRepository->getServiceScope($request->scope);

            if ($category->parent_id == null) {
                if ((int)$request->is_business) {
                    $services = $this->categoryRepository->getServicesOfCategory((Category::where('parent_id', $category->id)->publishedForBusiness()->orderBy('order')->get())->pluck('id')->toArray(), $location, $offset, $limit);
                } elseif ($request->is_b2b) {
                    $services = $this->categoryRepository->getServicesOfCategory(Category::where('parent_id', $category->id)->publishedForB2B()
                        ->orderBy('order')->get()->pluck('id')->toArray(), $location, $offset, $limit);
                } elseif ($request->is_ddn) {
                    $services = $this->categoryRepository->getServicesOfCategory(Category::where('parent_id', $category->id)->publishedForDdn()
                        ->orderBy('order')->get()->pluck('id')->toArray(), $location, $offset, $limit);
                } else {
                    $services = $this->categoryRepository->getServicesOfCategory($category->children->sortBy('order')->pluck('id'), $location, $offset, $limit);
                }
                $services = $this->serviceRepository->addServiceInfo($services, $scope);
            } else {
                $category->load(['services' => function ($q) use ($offset, $limit, $location) {
                    /*if (!(int)\request()->is_business || !(int)\request()->is_ddn) {
                        $q->whereNotIn('id', $this->serviceGroupServiceIds());

                    }*/
                    $q->where('is_add_on',0)->whereHas('locations', function ($query) use ($location) {
                        $query->where('locations.id', $location);
                    })->select(
                        'id', 'category_id', 'unit', 'name', 'bn_name', 'thumb',
                        'app_thumb', 'app_banner', 'short_description', 'description',
                        'banner', 'faqs', 'variables', 'variable_type', 'min_quantity', 'options_content',
                        'terms_and_conditions', 'features','is_inspection_service', 'is_add_on'
                    )->orderBy('order')->skip($offset)->take($limit);

                    if ((int)\request()->is_business) $q->publishedForBusiness();
                    elseif ((int)\request()->is_for_backend) $q->publishedForAll();
                    elseif ((int)\request()->is_b2b) $q->publishedForB2B();
                    elseif ((int)\request()->is_ddn) $q->publishedForDdn();
                    else $q->published();
                }]);
                $services = $category->services;
            }

            if ($location) {
                $services->load(['activeSubscription', 'locationServices' => function ($q) use ($location) {
                    $q->where('location_id', $location);
                }]);
            }

            if ($request->has('service_id')) {
                $services = $services->filter(function ($service) use ($request) {
                    return $request->service_id == $service->id;
                });
            }

            $subscriptions = collect();
            $final_services = collect();
            $service_ids = $services->pluck('id')->toArray();
            $slugs = UniversalSlugModel::where('sluggable_type', 'like', '%service')->whereIn('sluggable_id', $service_ids)->select('sluggable_id', 'slug')->get();
            $location_service_ids = [];
            foreach ($services->pluck('locationServices') as $location_service) {
                array_push($location_service_ids, $location_service->first() ? $location_service->first()->id : null);
            }
            $location_service_with_discounts = LocationService::whereIn('id', $location_service_ids)->select('id', 'location_id', 'service_id')
                ->whereHas('discounts', function ($q) {
                    $q->running();
                })->with(['discounts' => function ($q) {
                    $q->running();
                }])->get();
            foreach ($services as $key => $service) {
                /** @var LocationService $location_service */
                $location_service = $service->locationServices->first();
                $location_service_with_discount = $location_service_with_discounts->where('id', $location_service->id)->first();
                /** @var ServiceDiscount $discount */
                $discount = $location_service_with_discount ? $location_service_with_discount->discounts->first() : null;
                $prices = json_decode($location_service->prices);
                if ($prices === null) continue;
                $price_calculation->setService($service)->setLocationService($location_service);
                $upsell_calculation->setService($service)->setLocationService($location_service);

                if ($service->variable_type == 'Options') {
                    if (!$prices instanceof stdClass) throw new CorruptedPriceStructureException('Price mismatch in Service #' . $location_service->service_id . ' and location #' . $location_service->location_id, 400);
                    $service['option_prices'] = $this->formatOptionWithPrice($price_calculation, $prices, $upsell_calculation, $location_service);
                } else {
                    if ($prices instanceof stdClass) throw new CorruptedPriceStructureException('Price mismatch in Service #' . $location_service->service_id . ' and location #' . $location_service->location_id, 400);
                    $service['fixed_price'] = $price_calculation->getUnitPrice();
                    $service['fixed_upsell_price'] = $upsell_calculation->getAllUpsellWithMinMaxQuantity();
                }

                $service['discount'] = $discount ? [
                    'value' => (double)$discount->amount,
                    'is_percentage' => $discount->isPercentage(),
                    'cap' => (double)$discount->cap
                ] : null;
                $min_max_price->setService($service)->setLocationService($location_service);
                $service['max_price'] = $min_max_price->getMax();
                $service['min_price'] = $min_max_price->getMin();
                $service['addon'] = $service->crossSaleService ? [
                    'title' => $service->crossSaleService->title,
                    'description' => $service->crossSaleService->description,
                    'icon' => $service->crossSaleService->icon,
                    'category_id' => $category->id,
                    'service_id' => $service->crossSaleService->add_on_service_id
                ]: null;
                $service['is_add_on'] = $service->is_add_on;
                $service['terms_and_conditions'] = $service->terms_and_conditions ? json_decode($service->terms_and_conditions) : null;
                $service['features'] = $service->features ? json_decode($service->features) : null;
                $slug = $slugs->where('sluggable_id', $service->id)->first();
                $service['slug'] = $slug ? $slug->slug : null;

                /** @var ServiceSubscription $subscription */
                if ($subscription = $service->activeSubscription) {
                    $price_range = $approximate_price_calculator->setLocationService($location_service)->setSubscription($subscription)->getPriceRange();
                    $subscription = removeRelationsAndFields($subscription);
                    $subscription['max_price'] = $price_range['max_price'] > 0 ? $price_range['max_price'] : 0;
                    $subscription['min_price'] = $price_range['min_price'] > 0 ? $price_range['min_price'] : 0;
                    $subscription['unit'] = $service['unit'];
                    $subscription['thumb'] = $service['thumb'];
                    $subscription['banner'] = $service['banner'];
                    $subscription['offers'] = $subscription->getDiscountOffers();
                    $lowest_service_subscription_discount = $subscription->validDiscounts->sortBy('discount_amount')->first();
                    $subscription['discount'] = $lowest_service_subscription_discount ? [
                        'discount_amount' => $lowest_service_subscription_discount->discount_amount,
                        'is_discount_amount_percentage' => $lowest_service_subscription_discount->is_discount_amount_percentage,
                        'cap' => $lowest_service_subscription_discount->cap,
                        'min_discount_qty' => $lowest_service_subscription_discount->min_discount_qty,
                        'text' => $subscriptionDiscount->setServiceSubscriptionDiscount($lowest_service_subscription_discount)->getDiscountText()

                    ] : null;
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
                    removeRelationsAndFields($subscription);
                    $subscriptions->push($subscription);
                }
                removeRelationsAndFields($service);
                $final_services->push($service);
            }
            $services = $final_services;
            if ($services->count() > 0) {
                $parent_category = null;
                if ($category->parent_id != null) $parent_category = $category->parent()->select('id', 'name', 'slug')->first();
                $category = collect($category)->only(['id', 'name', 'slug', 'banner', 'parent_id', 'app_banner', 'service_title', 'is_auto_sp_enabled', 'min_order_amount', 'max_order_amount', 'is_vat_applicable', 'terms_and_conditions']);
                $version_code = (int)$request->header('Version-Code');
                $services = $this->serviceQuestionSet($services);
                if ($version_code && $version_code <= 30122 && $version_code <= 107) {
                    $services = $services->reject(function ($service) use ($version_code) {
                        return $service->subscription;
                    })->values()->all();
                }
                $category['parent_name'] = $parent_category ? $parent_category->name : null;
                $category['parent_slug'] = $parent_category ? $parent_category->slug : null;
                $category['terms_and_conditions'] = $category['terms_and_conditions'] ? json_decode($category['terms_and_conditions']) : null;
                $category['services'] = $services;
                $category['subscriptions'] = $subscriptions->sortBy('discount.discount_amount');
                $category['cross_sale'] = null;
                $category_model = Category::find($category['id']);
                $category['delivery_charge'] = $delivery_charge->setCategory($category_model)
                    ->setLocation(Location::find($location))->get();
                $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($category['delivery_charge']);
                $job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($category_model)->setCheckingParams($discount_checking_params)->calculate();
                /** @var Discount $delivery_discount */
                $delivery_discount = $job_discount_handler->getDiscount();

                $category['delivery_discount'] = $delivery_discount ? [
                    'value' => (double)$delivery_discount->amount,
                    'is_percentage' => $delivery_discount->is_percentage,
                    'cap' => (double)$delivery_discount->cap,
                    'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
                ] : null;
                $category['slug'] = $category_slug;
                $category['max_order_amount'] = $category['max_order_amount'] ? (double)$category['max_order_amount'] : null;

                if ($subscriptions->count()) {
                    $category['subscription_faq'] = $subscription_faq;
                }
                return api_response($request, $category, 200, ['category' => $category]);
            } else
                return api_response($request, null, 404);
        } else {
            return api_response($request, null, 404);
        }
    }

    /**
     * @param PriceCalculation $price_calculation
     * @param $prices
     * @param UpsellCalculation $upsell_calculation
     * @param LocationService $location_service
     * @return Collection
     */
    private function formatOptionWithPrice(PriceCalculation $price_calculation, $prices,
                                           UpsellCalculation $upsell_calculation, LocationService $location_service)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $option_array = explode(',', $key);
            $options->push([
                'option' => collect($option_array)->map(function ($key) {
                    return (int)$key;
                }),
                'price' => $price_calculation->setOption($option_array)->getUnitPrice(),
                'upsell_price' => $upsell_calculation->setOption($option_array)->getAllUpsellWithMinMaxQuantity()
            ]);
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
                $option_contents = $service->options_content ? json_decode($service->options_content, true) : [];
                foreach ($questions as $option_keys => &$question) {
                    $question = collect($question);
                    $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                    $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                    $option_key = $option_keys + 1;
                    $option_content = key_exists($option_key, $option_contents) ? $option_contents[$option_key] : [];
                    $explode_answers = explode(',', $question->get('answers'));
                    $contents = [];
                    $answer_contents = [];
                    foreach ($explode_answers as $answer_keys => $answer) {
                        $answer_key = $answer_keys + 1;
                        $value = key_exists($answer_key, $option_content) ? $option_content[$answer_key] : null;
                        array_push($contents, $value);
                        array_push($answer_contents, ['key' => $answer_keys, 'content' => $value]);
                    }
                    $question->put('answers', $explode_answers);
                    $question->put('contents', $contents);
                    $question->put('answer_contents', $answer_contents);
                }
                if (count($questions) == 1) {
                    $questions[0]->put('input_type', 'selectbox');
                }
            }
            $service['questions'] = $questions;
            $service['faqs'] = json_decode($service->faqs);
            array_forget($service, 'variables');
            array_forget($service, 'options_content');
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

    public function getReviews($category, Request $request, CacheAside $cache_aside, ReviewCacheRequest $review_cache_request)
    {
        $review_cache_request->setCategoryId($category);
        $data = $cache_aside->setCacheRequest($review_cache_request)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
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
