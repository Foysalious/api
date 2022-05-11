<?php namespace Sheba\Business\LiveTracking\Employee;

use App\Models\BusinessMember;
use Carbon\Carbon;

class LiveTrackingDetails
{
    /*** @var BusinessMember */
    private $businessMember;
    private $trackingLocations;

    public function __construct(BusinessMember $business_member, $tracking_locations)
    {
        $this->businessMember = $business_member;
        $this->trackingLocations = $tracking_locations;
    }

    public function get()
    {
        $employee_details = $this->getEmployeeDetails();
        $location_details = $this->getLocationDetails();
        return ['employee' => $employee_details, 'timeline' => $location_details['timeline'], 'data_missing_count' => $location_details['data_missing_count']];
    }

    private function getEmployeeDetails()
    {
        $role =  $this->businessMember->role;
        $profile = $this->businessMember->profile();
        return [
            'employee_id' => $this->businessMember->employee_id,
            'employee_name' => $profile->name,
            'employee_role' =>$role ? $role->name : null,
            'employee_department' =>$role ? $role->businessDepartment->name : null,
            'pro_pic' => $profile->pro_pic
        ];
    }

    private function getLocationDetails()
    {
        $data = [];
        $date_missing = 0;
        foreach ($this->trackingLocations as $tracking_location) {
            $location = $tracking_location->location;
            if (!$location) $date_missing++;
            $data[] = [
                'time' => Carbon::parse($tracking_location->time)->format('h:i A'),
                'address' => $location  ? $location->address : null,
                'location' => $location ? [
                    'lat' => $location->lat,
                    'lng' => $location->lng
                ] : null,
                'log' => $tracking_location->log
            ];
        }
        $location_data['timeline'] = $data;
        $location_data['data_missing_count'] = $date_missing;
        return $location_data;
    }
}
