<?php namespace App\Transformers;

use App\Models\LocationService;
use App\Models\Service;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Sheba\LocationService\PriceCalculation;
use Sheba\Services\Type;

class ServiceV2Transformer extends TransformerAbstract
{
    /** @var LocationService $locationService */
    private $locationService;
    /** @var PriceCalculation $priceCalculation */
    private $priceCalculation;

    /**
     * ServiceV2Transformer constructor.
     * @param LocationService $location_service
     * @param PriceCalculation $price_calculation
     */
    public function __construct(LocationService $location_service, PriceCalculation $price_calculation)
    {
        $this->locationService = $location_service;
        $this->priceCalculation = $price_calculation;
    }

    public function transform(Service $service)
    {
        $prices = json_decode($this->locationService->prices);
        $discount = $this->locationService->discounts()->running()->first();
        $this->priceCalculation->setLocationService($this->locationService);

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
