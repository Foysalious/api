<?php namespace Sheba\PriceCalculation;

use App\Models\Category;
use Sheba\LocationService\PriceCalculation;

class PriceCalculationFactory
{
    protected $category;

    /**
     * @param Category $category
     * @return PriceCalculationFactory
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    public function get()
    {
        if ($this->category->isRentACarOutsideCity()) return app(CarRentalPriceCalculation::class);
        else return app(PriceCalculation::class);
    }
}