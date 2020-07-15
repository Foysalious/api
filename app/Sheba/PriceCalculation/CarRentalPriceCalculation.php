<?php namespace Sheba\PriceCalculation;


use App\Models\CarRentalPrice;
use App\Models\Service;
use stdClass;
use Sheba\LocationService\CorruptedPriceStructureException;

class CarRentalPriceCalculation extends PriceCalculationAbstract
{
    protected $pickupThanaId;
    protected $destinationThanaId;
    protected $carRentalPrice;

    /**
     * @param $id
     * @return CarRentalPriceCalculation
     */
    public function setPickupThanaId($id)
    {
        $this->pickupThanaId = $id;
        return $this;
    }

    /**
     * @param $id
     * @return CarRentalPriceCalculation
     */
    public function setDestinationThanaId($id)
    {
        $this->destinationThanaId = $id;
        return $this;
    }

    /**
     * @return float|int
     * @throws CorruptedPriceStructureException
     */
    public function getTotalOriginalPrice()
    {
        $unit_price = $this->getUnitPrice();
        $surcharge = $this->getSurcharge();
        $surcharge_amount = $surcharge ? ($surcharge->isPercentage() ? ($unit_price * $surcharge->amount) / 100 : $surcharge->amount) : 0;
        $unit_price_with_surcharge = $unit_price + $surcharge_amount;
        return $unit_price_with_surcharge * $this->quantity;
    }

    /**
     * @return float|null
     * @throws CorruptedPriceStructureException
     */
    public function getUnitPrice()
    {
        $service = $this->getService();
        $this->carRentalPrice = CarRentalPrice::where('pickup_thana_id', $this->pickupThanaId)->where('destination_thana_id', $this->destinationThanaId)->first();

        if(!$this->carRentalPrice) return null;

        if ($service->isFixed()) return $this->getFixedPrice($this->carRentalPrice->prices);
        return $this->getOptionPrice($this->carRentalPrice->prices);
    }

    /**
     * @param $prices
     * @return float
     * @throws CorruptedPriceStructureException
     */
    protected function getFixedPrice($prices)
    {
        if ($prices instanceof stdClass) $this->throwPriceStructureException();
        return (double)$this->carRentalPrice->prices;
    }

    /**
     * @param $prices
     * @return float|null
     * @throws CorruptedPriceStructureException
     */
    protected function getOptionPrice($prices)
    {
        $option = implode(',', $this->option);
        $prices = json_decode($prices);
        if (!$prices instanceof stdClass) $this->throwPriceStructureException();
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return (double)$price;
            }
        }
        return null;
    }

    /**
     * @throws CorruptedPriceStructureException
     */
    protected function throwPriceStructureException()
    {
        throw new CorruptedPriceStructureException('Price mismatch in Service' . $this->service->id . ', Pickup Thana #' . $this->pickupThanaId . ', Destination Thana #' . $this->destinationThanaId, 400);
    }

    /**
     * @return Service
     */
    protected function getService()
    {
        return $this->service;
    }

}