<?php namespace Sheba\PartnerList;


use App\Models\Category;
use App\Models\Customer;
use App\Models\Review;
use Sheba\Checkout\Services\ServiceObject;
use Sheba\Location\Geo;

class Recommended
{
    /** @var Customer */
    private $customer;
    /** @var ServiceObject */
    private $serviceObject;
    /** @var Geo */
    private $geo;

    /**
     * @param Customer $customer
     * @return Recommended
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param $service_object
     * @return Recommended
     */
    public function setService(ServiceObject $service_object)
    {
        $this->serviceObject = $service_object;
        return $this;
    }

    /**
     * @param Geo $geo
     * @return Recommended
     */
    public function setGeo($geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function get()
    {
        $reviews = Review::where([['category_id', $this->serviceObject->getCategory()->id], ['customer_id', $this->customer->id], ['rating', 5]])->orderBy('id', 'desc')->get();
    }


}