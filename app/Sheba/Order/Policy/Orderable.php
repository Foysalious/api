<?php namespace Sheba\Order\Policy;


use App\Models\Category;
use App\Models\LocationService;
use Illuminate\Support\Collection;

abstract class Orderable
{

    /** @var Category */
    protected $category;
    /** @var Collection */
    protected $locationServices;

    /**
     * @param Category $category
     * @return Orderable
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @param LocationService[] $locationServices
     * @return $this
     */
    public function setLocationServices($locationServices)
    {
        $this->locationServices = $locationServices;
        return $this;
    }

    abstract public function canOrder();
}