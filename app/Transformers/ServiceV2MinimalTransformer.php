<?php namespace App\Transformers;

use App\Models\LocationService;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\LocationService\PriceCalculation;
use Sheba\Services\Type;

class ServiceV2MinimalTransformer extends TransformerAbstract
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

    /**
     * @param array $selected_service
     * @return array
     */
    public function transform(array $selected_service)
    {
        /** @var ServiceDiscount $discount */
        $discount = $this->locationService->discounts()->running()->first();
        $this->priceCalculation->setLocationService($this->locationService);

        $data = [
            'discount'      => $discount ? [
                'value' => (double)$discount->amount,
                'is_percentage' => $discount->isPercentage(),
                'cap' => (double)$discount->cap
            ] : null,
        ];
        if ($selected_service["variable_type"] == Type::FIXED)
            $data['unit_price'] = $this->priceCalculation->getUnitPrice();
        if ($selected_service["variable_type"] == Type::OPTIONS) {
            $data['unit_price'] = $this->priceCalculation->setOption($selected_service["option"])->getUnitPrice();
        }

        return $data;
    }
}
