<?php namespace Sheba\Business\Scheduler;


use App\Models\BusinessDepartment;

class VehicleScheduler
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

    public function getFreeVehicles()
    {
       dd( $this->businessDepartment);
//        BusinessTrip::whereIn('resource_id', $resource_ids)
//            ->where(function ($query) use ($start_time, $end_time) {
//                $query->where([['start', '>', $start_time], ['start', '<', $end_time]]);
//                $query->orwhere([['end', '>', $start_time], ['end', '<', $end_time]]);
//                $query->orwhere([['start', '<', $start_time], ['end', '>', $start_time]]);
//                $query->orwhere([['start', '<', $end_time], ['end', '>', $end_time]]);
//                $query->orwhere([['start', $start_time], ['end', $end_time]]);
//            })->get();
    }
}