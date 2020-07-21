<?php namespace App\Transformers;

use App\Models\LocationService;
use App\Models\Service;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountRules;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\CorruptedPriceStructureException;
use Sheba\LocationService\PriceCalculation;
use Sheba\Services\Type;

class ServiceV2Transformer extends TransformerAbstract
{
    /** @var LocationService $locationService */
    private $locationService;
    /** @var PriceCalculation $priceCalculation */
    private $priceCalculation;
    /** @var DeliveryCharge $deliveryCharge */
    private $deliveryCharge;
    /** @var JobDiscountHandler $jobDiscountHandler */
    private $jobDiscountHandler;

    /**
     * ServiceV2Transformer constructor.
     * @param LocationService $location_service
     * @param PriceCalculation $price_calculation
     * @param DeliveryCharge $delivery_charge
     * @param JobDiscountHandler $job_discount_handler
     */
    public function __construct(LocationService $location_service, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler)
    {
        $this->locationService = $location_service;
        $this->priceCalculation = $price_calculation;
        $this->deliveryCharge = $delivery_charge;
        $this->jobDiscountHandler = $job_discount_handler;
    }

    /**
     * @param Service $service
     * @return array
     * @throws InvalidDiscountType
     * @throws CorruptedPriceStructureException
     */
    public function transform(Service $service)
    {
        $prices = json_decode($this->locationService->prices);
        /** @var ServiceDiscount $discount */
        $discount = $this->locationService->discounts()->running()->first();
        $this->priceCalculation->setLocationService($this->locationService);

        $original_delivery_charge = $this->deliveryCharge->setCategory($service->category)
            ->setLocation($this->locationService->location)->get();
        $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($original_delivery_charge);
        $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($service->category)->setCheckingParams($discount_checking_params)->calculate();
        /** @var Discount $delivery_discount */
        $delivery_discount = $this->jobDiscountHandler->getDiscount();

        $data = [
            'id'            => (int)$service->id,
            'name'          => $service->name,
            'type'          => $service->variable_type,
            'min_quantity'  => $service->min_quantity,
            'faqs'          => json_decode($service->faqs),
            'description'   => $service->description,
            'discount'      => $discount ? [
                'value' => (double)$discount->amount,
                'is_percentage' => $discount->isPercentage(),
                'cap' => (double)$discount->cap
            ] : null,
            'delivery_charge' => $original_delivery_charge,
            'delivery_discount' => $delivery_discount ? [
                'value' => (double)$delivery_discount->amount,
                'is_percentage' => $delivery_discount->is_percentage,
                'cap' => (double)$delivery_discount->cap,
                'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
            ] : null
        ];
        if ($service->variable_type == Type::FIXED)
            $data['fixed_price'] = $this->priceCalculation->getUnitPrice();
        if ($service->variable_type == Type::OPTIONS) {
            $variables = json_decode($service->variables);
            $data['options']       = $this->getOption($variables);
            $data['option_prices'] = $this->formatOptionWithPrice($prices);
        }

        return $data;
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
                }), 'price' => $this->priceCalculation->setOption($option_array)->getUnitPrice()
            ]);
        }
        return $options;
    }

    /**
     * @param $variables
     * @return mixed
     */
    private function getOption($variables)
    {
        $questions = $variables->options;
        foreach ($questions as &$question) {
            $question = collect($question);
            $explode_answers = explode(',', $question->get('answers'));
            $question->put('answers', $explode_answers);
        }

        return $questions;
    }
}
