<?php namespace Sheba\Business\LiveTracking\Employee;

use App\Models\BusinessMember;
use Carbon\Carbon;

class LiveTrackingDetailsReport
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
        return ['employee' => $employee_details, 'timeline' => $location_details];
    }

    private function getEmployeeDetails()
    {
        $role =  $this->businessMember->role;
        $profile = $this->businessMember->profile();
        return [
            'employee_id' => $this->businessMember->employee_id,
            'employee_name' => $profile->name,
            'employee_email' => $profile->email,
            'employee_mobile' => $this->businessMember->mobile,
            'employee_role' =>$role ? $role->name : null,
            'employee_department' =>$role ? $role->businessDepartment->name : null
        ];
    }

    private function getLocationDetails()
    {
        $data = [];
        foreach ($this->trackingLocations as $tracking_location) {
            $location = $tracking_location->location;
            $data[$tracking_location->date->toDateString()][] = [
                'time' => Carbon::parse($tracking_location->time)->format('h:i A'),
                'address' => $location->address,
                'location' => [
                    'lat' => $location->lat,
                    'lng' => $location->lng
                ]
            ];
        }
        return $data;
    }
}
