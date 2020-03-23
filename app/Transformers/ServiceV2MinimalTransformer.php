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


    public function __construct(PriceCalculation $price_calculation)
    {
        $this->priceCalculation = $price_calculation;
    }

    public function setLocationService(LocationService $location_service)
    {
        $this->locationService = $location_service;
        return $this;
    }

    /**
     * @param array $selected_service
     * @return array
     */
    public function transform(array $selected_service)
    {
        /** @var ServiceDiscount $discount */
        $discount = $this->locationService ? $this->locationService->discounts()->running()->first() : null;
        if ($this->locationService) $this->priceCalculation->setLocationService($this->locationService);
        $data = [
            'is_same_service' => 1,
            'discount' => $discount ? [
                'value' => (double)$discount->amount,
                'is_percentage' => $discount->isPercentage(),
                'cap' => (double)$discount->cap
            ] : null,
        ];
        if ($selected_service["variable_type"] == Type::FIXED) {
            if ($this->locationService && $this->locationService->service->isFiexed()) {
                $data['unit_price'] = $this->priceCalculation->getUnitPrice();
            } else {
                $data['unit_price'] = null;
                $data['is_same_service'] = 0;
            }
        }
        if ($selected_service["variable_type"] == Type::OPTIONS) {
            if ($this->locationService && $this->locationService->service->isOptions()) {
                $data['unit_price'] = $this->priceCalculation->setOption($selected_service["option"])->getUnitPrice();
            } else {
                $data['unit_price'] = null;
                $data['is_same_service'] = 0;
            }
        }

        return $data;
    }
}
