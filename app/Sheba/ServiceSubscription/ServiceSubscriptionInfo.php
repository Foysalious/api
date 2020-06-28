<?php namespace Sheba\ServiceSubscription;

use App\Models\LocationService;
use App\Models\ServiceSubscription;
use App\Models\ServiceSubscriptionDiscount;
use Illuminate\Support\Collection;
use Sheba\LocationService\CorruptedPriceStructureException;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Services\ServiceQuestionSet;
use Sheba\Subscription\ApproximatePriceCalculator;
use Sheba\Services\ServiceSubscriptionDiscount as SubscriptionDiscount;

class ServiceSubscriptionInfo
{
    protected $serviceSubscription;
    protected $locationService;
    protected $approximatePriceCalculator;
    protected $price_calculation;
    protected $serviceQuestionSet;
    protected $upsell_calculation;
    protected $serviceSubscriptionDiscount;

    public function __construct(ApproximatePriceCalculator $approximatePriceCalculator, PriceCalculation $price_calculation, ServiceQuestionSet $serviceQuestionSet, UpsellCalculation $upsell_calculation, SubscriptionDiscount $subscriptionDiscount)
    {
        $this->approximatePriceCalculator = $approximatePriceCalculator;
        $this->price_calculation = $price_calculation;
        $this->serviceQuestionSet = $serviceQuestionSet;
        $this->upsell_calculation = $upsell_calculation;
        $this->serviceSubscriptionDiscount = $subscriptionDiscount;
    }


    /**
     * @param ServiceSubscription $serviceSubscription
     * @return ServiceSubscriptionInfo
     */
    public function setServiceSubscription(ServiceSubscription $serviceSubscription)
    {
        $this->serviceSubscription = $serviceSubscription;
        return $this;
    }

    /**
     * @param LocationService $locationService
     * @return ServiceSubscriptionInfo
     */
    public function setLocationService(LocationService $locationService)
    {
        $this->locationService = $locationService;
        return $this;
    }

    /**
     * @return mixed
     * @throws CorruptedPriceStructureException
     */
    public function getServiceSubscriptionInfo()
    {
        $serviceSubscription = $this->serviceSubscription;
        $price_range = $this->approximatePriceCalculator->setLocationService($this->locationService)->setSubscription($this->serviceSubscription)->getPriceRange();
        $serviceSubscription['max_price'] = $price_range['max_price'] > 0 ? $price_range['max_price'] : 0;
        $serviceSubscription['min_price'] = $price_range['min_price'] > 0 ? $price_range['min_price'] : 0;
        $serviceSubscription['price_applicable_for'] = $this->approximatePriceCalculator->getSubscriptionType();
        $serviceSubscription['thumb'] = $this->serviceSubscription->service['thumb'];
        $serviceSubscription['banner'] = $this->serviceSubscription->service['banner'];
        $serviceSubscription['unit'] = $this->serviceSubscription->service['unit'];
        $serviceSubscription['service_min_quantity'] = $this->serviceSubscription->service['min_quantity'];
        $serviceSubscription['offers'] = $this->serviceSubscription->getDiscountOffers();
        $serviceSubscription['category_id'] = $this->serviceSubscription->service->category->id;
        $serviceSubscription['is_auto_sp_enabled'] = $this->serviceSubscription->service->category->is_auto_sp_enabled;
        $questionSet = $this->serviceQuestionSet->setServices($this->serviceSubscription->service()->select(
            'id', 'category_id', 'unit', 'name', 'bn_name', 'thumb',
            'app_thumb', 'app_banner', 'short_description', 'description',
            'banner', 'faqs', 'variables', 'variable_type', 'min_quantity', 'options_content',
            'terms_and_conditions', 'features', 'structured_contents'
        )->get())->getServiceQuestionSet()->first();

        $serviceSubscription['service_details'] = $questionSet;
        $prices = json_decode($this->locationService->prices);
        $this->price_calculation->setService($this->serviceSubscription->service)->setLocationService($this->locationService);
        $this->upsell_calculation->setService($this->serviceSubscription->service)->setLocationService($this->locationService);

        $serviceSubscription['fixed_price'] = $this->serviceSubscription->service->isFixed() && $this->locationService ? $this->price_calculation->getUnitPrice() : null;
        $serviceSubscription['fixed_upsell_price'] = $this->serviceSubscription->service->isFixed() && $this->locationService ? $this->upsell_calculation->getAllUpsellWithMinMaxQuantity() : null;
        $serviceSubscription['option_prices'] = isset($prices) && $this->locationService ? $this->serviceSubscription->service->isOptions() ? $this->formatOptionWithPrice($prices) : null :null;

        $lowest_service_subscription_discount = $this->serviceSubscription->discounts->first();
        $serviceSubscription['discount'] = $lowest_service_subscription_discount ? [
            'discount_amount' => $lowest_service_subscription_discount->discount_amount,
            'is_discount_amount_percentage' => $lowest_service_subscription_discount->isPercentage(),
            'cap' => $lowest_service_subscription_discount->cap,
            'min_discount_qty' => $lowest_service_subscription_discount->min_discount_qty,
        ] : null;

        /** @var $discount ServiceSubscriptionDiscount $weekly_discount */
        $weekly_discount = $this->serviceSubscription->discounts()->where('subscription_type', 'weekly')->valid()->first();
        /** @var $discount ServiceSubscriptionDiscount $monthly_discount */
        $monthly_discount = $this->serviceSubscription->discounts()->where('subscription_type', 'monthly')->valid()->first();

        $serviceSubscription['weekly_discount'] = $weekly_discount ? [
            'value' => (double)$weekly_discount->discount_amount,
            'is_percentage' => $weekly_discount->isPercentage(),
            'cap' => (double)$weekly_discount->cap
        ] : null;
        $serviceSubscription['monthly_discount'] = $monthly_discount ? [
            'value' => (double)$monthly_discount->discount_amount,
            'is_percentage' => $monthly_discount->isPercentage(),
            'cap' => (double)$monthly_discount->cap
        ] : null;
        removeRelationsAndFields($serviceSubscription);
        return $serviceSubscription;
    }

    /**
     * @param $prices
     * @return Collection
     * @throws CorruptedPriceStructureException
     */
    private function formatOptionWithPrice($prices)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $option_array = explode(',', $key);
            $options->push([
                'option' => collect($option_array)->map(function ($key) {
                    return (int)$key;
                }),
                'price' => $this->price_calculation->setOption($option_array)->getUnitPrice(),
                'upsell_price' => $this->upsell_calculation->setOption($option_array)->getAllUpsellWithMinMaxQuantity()
            ]);
        }
        return $options;
    }
}