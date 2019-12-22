<?php namespace Sheba\Business\Scheduler;


use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessTrip;

class TripScheduler
{
    private $startDate;
    private $endDate;
    private $businessDepartment;
    private $business;

    public function setStartDate($start_date)
    {
        $this->startDate = $start_date;
        return $this;
    }

    public function setEndDate($end_date)
    {
        $this->endDate = $end_date;
        return $this;
    }

    public function setBusinessDepartment(BusinessDepartment $business_department)
    {
        $this->businessDepartment = $business_department;
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getFreeVehicles()
    {
        $vehicle_ids = $this->businessDepartment->vehicles->pluck('id')->toArray();
        $businessDepartment = $this->businessDepartment;
        $this->businessDepartment->business->load('hiredVehicles.vehicle');
        $hired_vehicles = $this->businessDepartment->business->hiredVehicles->filter(function ($hVehicle) use ($businessDepartment) {
            return $hVehicle->vehicle && $hVehicle->vehicle->business_department_id == $businessDepartment->id;
        });
        $hired_vehicle_ids = $hired_vehicles->count() > 0 ? $hired_vehicles->pluck('vehicle_id')->toArray() : [];
        $vehicle_ids = array_unique(array_merge($vehicle_ids, $hired_vehicle_ids));
        $booked_trips = BusinessTrip::whereIn('vehicle_id', $vehicle_ids)
            ->where(function ($query) {
                $query->where([['start_date', '>', $this->startDate], ['start_date', '<', $this->endDate]]);
                $query->orwhere([['end_date', '>', $this->startDate], ['end_date', '<', $this->endDate]]);
                $query->orwhere([['start_date', '<', $this->startDate], ['end_date', '>', $this->startDate]]);
                $query->orwhere([['start_date', '<', $this->endDate], ['end_date', '>', $this->endDate]]);
                $query->orwhere([['start_date', $this->startDate], ['end_date', $this->endDate]]);
            })->get();
        return collect($vehicle_ids)->diff($booked_trips->pluck('vehicle_id')->unique());
    }

    public function getFreeDrivers()
    {
        $this->business->load('members.profile.driver');
        $driver_ids = [];
        foreach ($this->business->members as $member) {
            if ($member->profile->driver) {
                array_push($driver_ids, $member->profile->driver->id);
            }
        }
        $hired_drivers = $this->business->hiredDrivers;
        $hired_drivers_ids = $hired_drivers->count() > 0 ? $hired_drivers->pluck('driver_id')->toArray() : [];
        $driver_ids = array_unique(array_merge($driver_ids, $hired_drivers_ids));
        $booked_trips = BusinessTrip::whereIn('driver_id', $driver_ids)
            ->where(function ($query) {
                $query->where([['start_date', '>', $this->startDate], ['start_date', '<', $this->endDate]]);
                $query->orwhere([['end_date', '>', $this->startDate], ['end_date', '<', $this->endDate]]);
                $query->orwhere([['start_date', '<', $this->startDate], ['end_date', '>', $this->startDate]]);
                $query->orwhere([['start_date', '<', $this->endDate], ['end_date', '>', $this->endDate]]);
                $query->orwhere([['start_date', $this->startDate], ['end_date', $this->endDate]]);
            })->get();
        return collect($driver_ids)->diff($booked_trips->pluck('driver_id')->unique());
    }

}
