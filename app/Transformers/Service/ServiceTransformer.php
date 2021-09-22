<?php namespace App\Transformers\Service;

use Sheba\Dal\Category\Category;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Partnership\Partnership;
use Sheba\Dal\Service\Service;
use Sheba\Service\MinMaxPrice;
use Sheba\Service\ServiceQuestion;
use League\Fractal\TransformerAbstract;
use DB;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Dal\CrosssaleService\Model as CrosssaleServiceModel;

class ServiceTransformer extends TransformerAbstract
{
    private $serviceQuestion;
    private $priceCalculation;
    private $upsellCalculation;
    private $deliveryCharge;
    private $jobDiscountHandler;
    private $locationService;
    private $minMaxPrice;

    public function __construct(ServiceQuestion $service_question, PriceCalculation $price_calculation, UpsellCalculation $upsell_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler, MinMaxPrice $min_max_price)
    {
        $this->serviceQuestion = $service_question;
        $this->priceCalculation = $price_calculation;
        $this->upsellCalculation = $upsell_calculation;
        $this->deliveryCharge = $delivery_charge;
        $this->jobDiscountHandler = $job_discount_handler;
        $this->minMaxPrice = $min_max_price;
    }

    public function setLocationService(LocationService $locationService)
    {
        $this->locationService = $locationService;
        return $this;
    }

    public function transform(Service $service)
    {
        /** @var Category $category */
        $category = $service->category;
        $usps = $service->usps()->select('name')->get();
        /** @var Partnership $partnership */
        $partnership = $service->partnership;
        $galleries = $service->galleries()->select('id', DB::Raw('thumb as image'))->get();
        $blog_posts = $service->blogPosts()->select('id', 'title', 'short_description', DB::Raw('thumb as image'), 'target_link')->get();
        $this->serviceQuestion->setService($service);
        /** @var CrosssaleServiceModel $cross_sale_service */
        $cross_sale_service = $category->crossSaleService;
        $cross_sale = $cross_sale_service ? [
            'title' => $cross_sale_service->title,
            'description' => $cross_sale_service->description,
            'icon' => $cross_sale_service->icon,
            'category_id' => $cross_sale_service->category_id,
            'service_id' => $cross_sale_service->service_id
        ] : null;
        $this->minMaxPrice->setService($service);
        if($this->locationService) {
            $delivery_charge = $this->deliveryCharge->setCategory($category)->setLocation($this->locationService->location)->get();
            $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($delivery_charge);
            $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($category)->setCheckingParams($discount_checking_params)->calculate();
            /** @var Discount $delivery_discount */
            $delivery_discount = $this->jobDiscountHandler->getDiscount();
            $delivery_discount = $delivery_discount ? [
                'value' => (double)$delivery_discount->amount,
                'is_percentage' => $delivery_discount->is_percentage,
                'cap' => (double)$delivery_discount->cap,
                'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
            ] : null;
            /** @var ServiceDiscount $discount */
            $discount = $this->locationService->discounts()->running()->first();
            $prices = json_decode($this->locationService->prices);
            $this->priceCalculation->setLocationService($this->locationService);
            $this->upsellCalculation->setLocationService($this->locationService);
            $this->minMaxPrice->setLocationService($this->locationService);
        }
        $reviews = $category->reviews()->selectRaw("count(DISTINCT(reviews.id)) as total_ratings,avg(reviews.rating) as avg_rating")->groupBy('reviews.category_id')->first();
        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->getSlug(),
            'thumb' => $service->thumb,
            'app_thumb' => $service->app_thumb,
            'app_banner' => $service->app_banner,
            'banner' => $service->banner,
            'variable_type' => $service->variable_type,
            'min_quantity' => $service->min_quantity,
            'is_inspection_service' => $service->is_inspection_service,
            'unit' => $service->unit,
            'questions' => $this->serviceQuestion->get(),
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->getSlug(),
                'cross_sale' => $cross_sale,
                'delivery_discount' => isset($delivery_discount) ? $delivery_discount : null,
                'delivery_charge' => isset($delivery_charge) ? $delivery_charge : null,
                'is_auto_sp_enabled' => $category->is_auto_sp_enabled,
                'min_order_amount' => (double) $category->min_order_amount
            ],
            'fixed_price' => $service->isFixed() && $this->locationService ? $this->priceCalculation->getUnitPrice() : null,
            'fixed_upsell_price' => $service->isFixed() && $this->locationService ? $this->upsellCalculation->getAllUpsellWithMinMaxQuantity() : null,
            'option_prices' => isset($prices) && $this->locationService ? $service->isOptions() ? $this->formatOptionWithPrice($prices) : null : null,
            'min_price' => $this->locationService ? $this->minMaxPrice->getMin() : null,
            'max_price' => $this->locationService ? $this->minMaxPrice->getMax() : null,
            'min_price' => $this->minMaxPrice->getMin(),
            'max_price' => $this->minMaxPrice->getMax(),
            'discount' => isset($discount) && $discount ? [
                'value' => (double)$discount->amount,
                'is_percentage' => $discount->isPercentage(),
                'cap' => (double)$discount->cap
            ] : null,
            'usp' => count($usps) > 0 ? $usps->pluck('name')->toArray() : null,
            'overview' => $service->contents ? $service->contents : null,
            'structured_description_bn' => ($service->structured_description_bn) ? json_decode($service->structured_description_bn) : null,
            'details' => $service->description,
            'partnership' => $partnership ? [
                'title' => $partnership->title,
                'short_description' => $partnership->short_description,
                'images' => count($partnership->slides) > 0 ? $partnership->slides->pluck('thumb') : []
            ] : null,
            'faqs' => $service->faqs ? json_decode($service->faqs) : null,
            'terms_and_conditions' => $service->terms_and_conditions ? json_decode($service->terms_and_conditions) : null,
            'features' => $service->features ? json_decode($service->features) : null,
            'gallery' => count($galleries) > 0 ? $galleries : null,
            'blog' => count($blog_posts) > 0 ? $blog_posts : null,
            'avg_rating' => $reviews ? round($reviews->avg_rating, 2) : null,
            'total_ratings' => $reviews ? $reviews->total_ratings : null,
            'total_services' => $service->published()->whereIn('category_id', array($category->id))->count()
        ];
    }

    private function formatOptionWithPrice($prices)
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
}
