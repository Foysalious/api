<?php namespace Sheba\PartnerList;


use App\Models\Customer;
use App\Models\Review;
use Sheba\Location\Geo;
use Sheba\ServiceRequest\ServiceRequestObject;

class Recommended
{
    /** @var Customer */
    private $customer;
    /** @var ServiceRequestObject[] */
    private $serviceRequestObject;
    /** @var Geo */
    private $geo;
    private $partnerListBuilder;
    private $partnerListDirector;

    public function __construct(PartnerListBuilder $partnerListBuilder, Director $director)
    {
        $this->partnerListBuilder = $partnerListBuilder;
        $this->partnerListDirector = $director;
    }

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
     * @param ServiceRequestObject[] $service_request_object
     * @return $this
     */
    public function setServiceRequestObject(array $service_request_object)
    {
        $this->serviceRequestObject = $service_request_object;
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
        $reviews = Review::where([['category_id', $this->getCategory()->id], ['customer_id', $this->customer->id], ['rating', 5]])->orderBy('id', 'desc')->get();;
        if (count($reviews) == 0) return null;
        $this->partnerListBuilder->setGeo($this->geo)->setPartnerIds($reviews->pluck('partner_id')->unique()->toArray())->setServiceRequestObjectArray($this->serviceRequestObject);
        $this->partnerListDirector->setBuilder($this->partnerListBuilder)->buildPartnerList();
        $partners = $this->partnerListBuilder->get();
        if (count($partners) == 0) return null;
        return $partners;
    }

    public function getCategory()
    {
        return $this->serviceRequestObject[0]->getCategory();
    }


}