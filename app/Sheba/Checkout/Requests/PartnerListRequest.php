<?php

namespace Sheba\Checkout\Requests;


use App\Models\Category;
use App\Models\Partner;
use App\Models\Service;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Checkout\Services\RentACarServiceObject;
use Sheba\Checkout\Services\ServiceObject;

class PartnerListRequest
{
    use Helpers;
    protected $request;
    /** @var Category */
    protected $selectedCategory;
    /** @var Partner */
    protected $selectedPartner;
    protected $selectedServices;
    protected $location;
    protected $scheduleDate;
    protected $scheduleTime;
    protected $scheduleStartTime;
    protected $scheduleEndTime;
    protected $lat;
    protected $lng;
    protected $skipAvailabilityCheck;
    protected $selectedServiceIds;
    protected $portalName;
    protected $homeDelivery;
    protected $onPremise;
    protected $subscriptionType;

    public function __get($name)
    {
        return $this->$name;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function setLocation($location_id)
    {
        $this->location = $location_id;
        return $this;
    }

    public function setGeo($lat, $lng)
    {
        $this->lat = (double)$lat;
        $this->lng = (double)$lng;
        return $this;
    }

    public function setScheduleDate($date)
    {
        $this->scheduleDate = is_array($date) ? $date : (json_decode($date) ? json_decode($date) : [$date]);
        return $this;
    }

    public function setScheduleTime($time)
    {
        $this->scheduleTime = $time;
        return $this;
    }

    public function setAvailabilityCheck($skipAvailabilityCheck)
    {
        $this->skipAvailabilityCheck = $skipAvailabilityCheck;
        return $this;
    }

    public function setPartner($partner)
    {
        $this->selectedPartner = $partner instanceof Partner ? $partner : Partner::find((int)$partner);
        return $this;
    }

    public function prepareObject()
    {
        $services = json_decode($this->request->services);
        $this->selectedCategory = $this->getCategory($services);
        $this->selectedServices = $this->getSelectedServices($services);
        $this->setTime();
        $this->setHomeDelivery();
        $this->setOnPremise();
        $this->setPortalName();
        $this->setSubscriptionType();
        if (!isset($this->location)) $this->setLocation($this->request->location);
        if (!isset($this->selectedPartner)) $this->setPartner($this->request->partner);
        if (!isset($this->scheduleDate)) $this->setScheduleDate($this->request->date);
        if (!isset($this->lat) && !isset($this->lng)) $this->setGeo($this->request->lat, $this->request->lng);
        if (!isset($this->skipAvailabilityCheck)) $this->setAvailabilityCheck((int)$this->request->skip_availability);
        $this->selectedServiceIds = $this->getServiceIds();
        $this->setRentACarPickUpGeo();
        if ($this->location) $this->location = $this->setRentACarLocation($this->selectedServices->first());
    }

    private function getCategory($services)
    {
        return (Service::find((int)$services[0]->id))->category;
    }

    /**
     * @param $services
     * @return ServiceObject[]|Collection
     */
    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $service = $this->selectedCategory->isRentCar() ? new RentACarServiceObject($service) : new ServiceObject($service);
            $selected_services->push($service);
        }
        return $selected_services;
    }

    private function getServiceIds()
    {
        $service_ids = collect();
        foreach ($this->selectedServices as $selected_service) {
            $service_ids->push($selected_service->id);
        }
        return $service_ids->unique()->toArray();
    }


    private function setPortalName()
    {
        $this->portalName = $this->request->header('portal-name');
    }

    private function setRentACarPickUpGeo()
    {
        if ($this->selectedCategory->isRentCar()) {
            $service = $this->selectedServices->first();
            if ($service->pickUpLocationLat && $service->pickUpLocationLng) {
                $this->setGeo($service->pickUpLocationLat, $service->pickUpLocationLng);
                $this->location = null;
            }
        }
    }

    private function setRentACarLocation(ServiceObject $service)
    {
        if ($this->location) {
            if ($service instanceof RentACarServiceObject) {
                $location = $this->api->get('/v2/locations/current?lat=' . $service->pickUpLocationLat . '&lng=' . $service->pickUpLocationLng);
                return $location ? $location->id : null;
            } else {
                return $this->location;
            }
        }
    }

    private function setHomeDelivery()
    {
        $this->homeDelivery = $this->request->has('has_home_delivery') ? (int)$this->request->get('has_home_delivery') : null;
    }

    private function setOnPremise()
    {
        $this->onPremise = $this->request->has('has_premise') ? (int)$this->request->get('has_premise') : null;
    }

    private function setTime()
    {
        if ($this->scheduleTime) return;
        if ($this->request->time) {
            $this->scheduleTime = $this->request->time;
            $time = explode('-', $this->scheduleTime);
            $this->scheduleStartTime = $time[0];
            $this->scheduleEndTime = $time[1];
        }
    }

    private function setSubscriptionType()
    {
        $this->subscriptionType = strtolower($this->request->subscription_type);
    }

    public function isWeeklySubscription()
    {
        if (!isset($this->subscriptionType)) $this->setSubscriptionType();
        return $this->subscriptionType == config('sheba.subscription_type.customer.weekly')['name'];
    }

    public function isMonthlySubscription()
    {
        if (!isset($this->subscriptionType)) $this->setSubscriptionType();
        return $this->subscriptionType == config('sheba.subscription_type.customer.monthly')['name'];
    }

    public function getSubscriptionQuantity()
    {
        return count($this->scheduleDate);
    }

    public function isValid()
    {
        if ($this->isWeeklySubscription()) return $this->getSubscriptionQuantity() >= $this->selectedServices->first()->serviceModel->subscription->min_weekly_qty;
        elseif ($this->isMonthlySubscription()) return $this->getSubscriptionQuantity() >= $this->selectedServices->first()->serviceModel->subscription->min_monthly_qty;
        else return true;
    }
}