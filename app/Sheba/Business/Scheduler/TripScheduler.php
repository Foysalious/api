<?php namespace Sheba\Business\Scheduler;


use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessTrip;

class TripScheduler
{
    private $startDate;
    private $endDate;
    private $businessDepartment;

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
        $trips = BusinessTrip::whereIn('vehicle_id', $vehicle_ids)
            ->where(function ($query) {
                $query->where([['start_date', '>', $this->startDate], ['start_date', '<', $this->endDate]]);
                $query->orwhere([['end_date', '>', $this->startDate], ['end_date', '<', $this->endDate]]);
                $query->orwhere([['start_date', '<', $this->startDate], ['end_date', '>', $this->startDate]]);
                $query->orwhere([['start_date', '<', $this->endDate], ['end_date', '>', $this->endDate]]);
                $query->orwhere([['start_date', $this->startDate], ['end_date', $this->endDate]]);
            })->get();
        return $trips->pluck('vehicle_id')->unique();
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
        $trips = BusinessTrip::whereIn('driver_id', $driver_ids)
            ->where(function ($query) {
                $query->where([['start_date', '>', $this->startDate], ['start_date', '<', $this->endDate]]);
                $query->orwhere([['end_date', '>', $this->startDate], ['end_date', '<', $this->endDate]]);
                $query->orwhere([['start_date', '<', $this->startDate], ['end_date', '>', $this->startDate]]);
                $query->orwhere([['start_date', '<', $this->endDate], ['end_date', '>', $this->endDate]]);
                $query->orwhere([['start_date', $this->startDate], ['end_date', $this->endDate]]);
            })->get();
        return $trips->pluck('driver_id')->unique();
    }

}