<?php namespace Sheba\PriceCalculation;


use App\Models\Service;
use App\Models\ServiceSurcharge;

abstract class PriceCalculationAbstract
{
    protected $option;
    protected $quantity;
    protected $minPrice;
    /** @var Service */
    protected $service;
    protected $upsellUnitPrice;

    public function __construct()
    {
        $this->quantity = 1;
    }

    /**
     * @param Service $service
     * @return $this
     */
    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @param array $option
     * @return $this
     */
    public function setOption(array $option)
    {
        $this->option = $option;
        return $this;
    }


    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    protected function setMinPrice($price)
    {
        $this->minPrice = $price;
        return $this;
    }

    public function setUpsellUnitPrice($upsellUnitPrice)
    {
        $this->upsellUnitPrice = $upsellUnitPrice;
        return $this;
    }

    public function getSurcharge()
    {
        return ServiceSurcharge::where('service_id', $this->service->id)->runningSurcharges()->first();
    }

    abstract public function getTotalOriginalPrice();

    abstract public function getUnitPrice();

    abstract protected function getFixedPrice($prices);

    abstract protected function getOptionPrice($prices);

    abstract protected function throwPriceStructureException();

    abstract protected function getService();
}