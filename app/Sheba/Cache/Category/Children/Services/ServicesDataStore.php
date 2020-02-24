<?php namespace Sheba\Cache\Category\Children\Services;


use App\Models\Category;
use App\Models\LocationService;
use App\Models\ServiceGroupService;
use App\Models\ServiceSubscription;
use App\Repositories\CategoryRepository;
use App\Repositories\ServiceRepository;
use Sheba\Cache\CacheRequest;
use Sheba\Cache\DataStoreObject;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Service\MinMaxPrice;
use Sheba\Subscription\ApproximatePriceCalculator;

class ServicesDataStore implements DataStoreObject
{
    /** @var ServicesCacheRequest */
    private $servicesCacheRequest;
    private $priceCalculation;
    private $deliveryCharge;
    private $jobDiscountHandler;
    private $upsellCalculation;
    private $minMaxPrice;
    private $approximatePriceCalculator;
    private $serviceRepository;
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository, ServiceRepository $serviceRepository, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                                JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation, MinMaxPrice $min_max_price, ApproximatePriceCalculator $approximate_price_calculator)
    {
        $this->priceCalculation = $price_calculation;
        $this->deliveryCharge = $delivery_charge;
        $this->jobDiscountHandler = $job_discount_handler;
        $this->upsellCalculation = $upsell_calculation;
        $this->minMaxPrice = $min_max_price;
        $this->approximatePriceCalculator = $approximate_price_calculator;
        $this->serviceRepository = $serviceRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function setCacheRequest(CacheRequest $cache_request)
    {
        $this->servicesCacheRequest = $cache_request;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        $subscription_faq = null;
        $category = $this->servicesCacheRequest->getCategoryId();
        $location = $this->servicesCacheRequest->getLocationId();
        /** @var Category $cat */
        $cat = Category::where('id', $category)->whereHas('locations', function ($q) use ($location) {
            $q->where('locations.id', $location);
        });

        if ($this->servicesCacheRequest->getIsBusiness()) {
            $category = $cat->publishedForBusiness()->first();
        } elseif ($this->servicesCacheRequest->getIsB2b()) {
            $category = $cat->publishedForB2B()->first();
        } elseif ($this->servicesCacheRequest->getIsDdn()) {
            $category = $cat->publishedForDdn()->first();
        } else {
            $category = $cat->published()->first();
        }
        if ($category == null) return null;
        if ($category != null) {
            $category_slug = $category->getSlug();
            $cross_sale_service = $category->crossSaleService;
            $offset = 0;
            $limit = 20;
            $scope = [];
            if ($this->servicesCacheRequest->getScope()) $scope = $this->serviceRepository->getServiceScope($this->servicesCacheRequest->getScope());

            if ($category->parent_id == null) {
                if ($this->servicesCacheRequest->getIsBusiness()) {
                    $services = $this->categoryRepository->getServicesOfCategory((Category::where('parent_id', $category->id)->publishedForBusiness()->orderBy('order')->get())->pluck('id')->toArray(), $location, $offset, $limit);
                } elseif ($this->servicesCacheRequest->getIsB2b()) {
                    $services = $this->categoryRepository->getServicesOfCategory(Category::where('parent_id', $category->id)->publishedForB2B()
                        ->orderBy('order')->get()->pluck('id')->toArray(), $location, $offset, $limit);
                } elseif ($this->servicesCacheRequest->getIsDdn()) {
                    $services = $this->categoryRepository->getServicesOfCategory(Category::where('parent_id', $category->id)->publishedForDdn()
                        ->orderBy('order')->get()->pluck('id')->toArray(), $location, $offset, $limit);
                } else {
                    $services = $this->categoryRepository->getServicesOfCategory($category->children->sortBy('order')->pluck('id'), $location, $offset, $limit);
                }
                $services = $this->serviceRepository->addServiceInfo($services, $scope);
            } else {
                $category->load(['services' => function ($q) use ($offset, $limit, $location) {
                    if (!(int)\request()->is_business || !(int)\request()->is_ddn) {
                        $q->whereNotIn('id', $this->serviceGroupServiceIds());

                    }
                    $q->whereHas('locations', function ($query) use ($location) {
                        $query->where('locations.id', $location);
                    })->select(
                        'id', 'category_id', 'unit', 'name', 'bn_name', 'thumb',
                        'app_thumb', 'app_banner', 'short_description', 'description',
                        'banner', 'faqs', 'variables', 'variable_type', 'min_quantity', 'options_content',
                        'terms_and_conditions', 'features'
                    )->orderBy('order')->skip($offset)->take($limit);

                    if ($this->servicesCacheRequest->getIsBusiness()) $q->publishedForBusiness();
                    elseif ($this->servicesCacheRequest->getIsForBackend()) $q->publishedForAll();
                    elseif ($this->servicesCacheRequest->getIsB2b()) $q->publishedForB2B();
                    elseif ($this->servicesCacheRequest->getIsDdn()) $q->publishedForDdn();
                    else $q->published();
                }]);
                $services = $category->services;
            }

            if ($location) {
                $services->load(['activeSubscription', 'locationServices' => function ($q) use ($location) {
                    $q->where('location_id', $location);
                }]);
            }

            if ($this->servicesCacheRequest->getServiceId()) {
                $services = $services->filter(function ($service) {
                    return $this->servicesCacheRequest->getServiceId() == $service->id;
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
                $this->priceCalculation->setService($service)->setLocationService($location_service);
                $this->upsellCalculation->setService($service)->setLocationService($location_service);

                if ($service->variable_type == 'Options') {
                    $service['option_prices'] = $this->formatOptionWithPrice($prices, $location_service);
                } else {
                    $service['fixed_price'] = $this->priceCalculation->getUnitPrice();
                    $service['fixed_upsell_price'] = $this->upsellCalculation->getAllUpsellWithMinMaxQuantity();
                }

                $service['discount'] = $discount ? [
                    'value' => (double)$discount->amount,
                    'is_percentage' => $discount->isPercentage(),
                    'cap' => (double)$discount->cap
                ] : null;
                $this->minMaxPrice->setService($service)->setLocationService($location_service);
                $service['max_price'] = $this->minMaxPrice->getMax();
                $service['min_price'] = $this->minMaxPrice->getMin();
                $service['terms_and_conditions'] = $service->terms_and_conditions ? json_decode($service->terms_and_conditions) : null;
                $service['features'] = $service->features ? json_decode($service->features) : null;
                $slug = $slugs->where('sluggable_id', $service->id)->first();
                $service['slug'] = $slug ? $slug->slug : null;

                /** @var ServiceSubscription $subscription */
                if ($subscription = $service->activeSubscription) {
                    $price_range = $this->approximatePriceCalculator->setLocationService($location_service)->setSubscription($subscription)->getPriceRange();
                    $subscription = removeRelationsAndFields($subscription);
                    $subscription['max_price'] = $price_range['max_price'] > 0 ? $price_range['max_price'] : 0;
                    $subscription['min_price'] = $price_range['min_price'] > 0 ? $price_range['min_price'] : 0;
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
                $final_services->push($service);
            }
            $services = $final_services;
            if ($services->count() == 0) return null;

            $parent_category = null;
            if ($category->parent_id != null) $parent_category = $category->parent()->select('id', 'name', 'slug')->first();
            $category = collect($category)->only(['id', 'name', 'slug', 'banner', 'parent_id', 'app_banner', 'service_title', 'is_auto_sp_enabled']);
            $services = $this->serviceQuestionSet($services);
            $category['parent_name'] = $parent_category ? $parent_category->name : null;
            $category['parent_slug'] = $parent_category ? $parent_category->slug : null;
            $category['services'] = $services;
            $category['subscriptions'] = $subscriptions;
            $category['cross_sale'] = $cross_sale_service ? [
                'title' => $cross_sale_service->title,
                'description' => $cross_sale_service->description,
                'icon' => $cross_sale_service->icon,
                'category_id' => $cross_sale_service->category_id,
                'service_id' => $cross_sale_service->service_id
            ] : null;
            $category_model = Category::find($category['id']);
            $category['delivery_charge'] = $this->deliveryCharge->setCategory($category_model)->get();
            $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($category['delivery_charge']);
            $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($category_model)->setCheckingParams($discount_checking_params)->calculate();
            /** @var Discount $delivery_discount */
            $delivery_discount = $this->jobDiscountHandler->getDiscount();

            $category['delivery_discount'] = $delivery_discount ? [
                'value' => (double)$delivery_discount->amount,
                'is_percentage' => $delivery_discount->is_percentage,
                'cap' => (double)$delivery_discount->cap,
                'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
            ] : null;
            $category['slug'] = $category_slug;

            if ($subscriptions->count()) {
                $category['subscription_faq'] = $subscription_faq;
            }
            return ['category' => $category];

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

    private function formatOptionWithPrice($prices,
                                           LocationService $location_service)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $option_array = explode(',', $key);
            $options->push([
                'option' => collect($option_array)->map(function ($key) {
                    return (int)$key;
                }),
                'price' => $this->priceCalculation->setOption($option_array)->getUnitPrice(),
                'upsell_price' => $this->upsellCalculation->setOption($option_array)->getAllUpsellWithMinMaxQuantity()
            ]);
        }
        return $options;
    }

    private function serviceGroupServiceIds()
    {
        $service_group_id = explode(',', config('sheba.service_group_ids'));
        $service_group_service_id = ServiceGroupService::whereIn('service_group_id', $service_group_id)->pluck('service_id')->toArray();
        return $service_group_service_id;
    }

    private function resolveInputTypeField($answers)
    {
        $answers = explode(',', $answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }

}