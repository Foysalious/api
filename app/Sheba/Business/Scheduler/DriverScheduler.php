<?php namespace Sheba\Business\Scheduler;


use App\Models\Business;
use App\Models\BusinessTrip;

class DriverScheduler
{
    private $startDate;
    private $endDate;
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
}