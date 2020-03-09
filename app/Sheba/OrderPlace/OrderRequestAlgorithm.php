<?php namespace Sheba\OrderPlace;


use App\Models\Customer;
use Illuminate\Support\Collection;

class OrderRequestAlgorithm
{
    /** @var Customer */
    private $customer;
    /** @var Collection */
    private $partners;
    private $orderCount;
    private $firstUserGroupPartners;
    private $secondUserGroupPartners;
    CONST NUMBER_OF_PARTNERS = 6;

    public function __construct()
    {
        $this->secondUserGroupPartners = collect();
        $this->firstUserGroupPartners = collect();
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setPartners($partners)
    {
        $this->partners = $partners;
        return $this;
    }

    public function getPartners()
    {
        return $this->partners->splice(0, self::NUMBER_OF_PARTNERS);
        $this->setOrderCount();
        if ($this->orderCount > 10) return $this->partners;
        if (3 >= $this->orderCount) return $this->filterPartnersForFirstUserGroup();
        if (3 < $this->orderCount && $this->orderCount <= 10) return $this->filterPartnersForSecondUserGroup();
    }

    public function setOrderCount()
    {
        $this->orderCount = $this->customer->orders()->count();
    }

    public function filterPartnersForFirstUserGroup()
    {
        foreach ($this->partners as $partner) {
            $completed_orders = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $rating = $partner->reviews->first() ? $partner->reviews->first()->avg_rating : 0;
            if ($rating <= 4.6 && $completed_orders > 50) $this->firstUserGroupPartners->push($partner);
        }
        return $this->firstUserGroupPartners->count() > 0 ? $this->firstUserGroupPartners : $this->partners;
    }


    public function filterPartnersForSecondUserGroup()
    {
        foreach ($this->partners as $partner) {
            $completed_orders = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $rating = $partner->reviews->first() ? $partner->reviews->first()->avg_rating : 0;
            if ($rating >= 4.20 && $rating <= 4.59 && $completed_orders > 10) $this->secondUserGroupPartners->push($partner);
        }
        return $this->secondUserGroupPartners->count() > 0 ? $this->secondUserGroupPartners : $this->partners;
    }

}