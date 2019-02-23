<?php

namespace Sheba\Checkout\Requests;


use App\Models\Category;
use App\Models\Service;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Sheba\Checkout\Services\RentACarServiceObject;
use Sheba\Checkout\Services\ServiceObject;

class PartnerListRequest
{
    use Helpers;
    private $request;
    /** @var Category */
    private $selectedCategory;
    private $selectedServices;
    public $location;
    private $scheduleDate;
    private $scheduleTime;
    private $scheduleStartTime;
    private $scheduleEndTime;
    private $lat;
    private $lng;
    private $skipAvailabilityCheck;
    private $selectedServiceIds;
    private $portalName;
    private $homeDelivery;
    private $onPremise;


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
        $this->scheduleDate = $date;
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

    public function prepareObject()
    {
        $services = json_decode($this->request->services);
        $this->selectedCategory = $this->getCategory($services);
        $this->selectedServices = $this->getSelectedServices($services);
        $this->location = $this->request->location;
        $this->scheduleDate = $this->request->date;
        $this->scheduleTime = $this->request->time;
        $this->scheduleStartTime = explode('-', $this->scheduleTime)[0];
        $this->scheduleEndTime = explode('-', $this->scheduleTime)[1];
        $this->setHomeDelivery();
        $this->setOnPremise();
        $this->setGeo($this->request->lat, $this->request->lng);
        $this->skipAvailabilityCheck = (int)$this->request->skip_availability;
        $this->selectedServiceIds = $this->getServiceIds();
        $this->setPortalName();
        $this->setRentACarPickUpGeo();
        if ($this->location) $this->location = $this->setRentACarLocation($this->selectedServices->first());
    }

    private function getCategory($services)
    {
        return (Service::find((int)$services[0]->id))->category;
    }

    /**
     * @param $services
     * @return ServiceObject[]
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

}