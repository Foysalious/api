<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class LiveTrackingListTransformer extends TransformerAbstract
{
    public function transform($tracking_locations)
    {
        $business_member = $tracking_locations->businessMember;
        $location = $tracking_locations->location;
        return [
            'employee' => $this->getEmployeeDetails($business_member),
            'business_member_id' => $tracking_locations->business_member_id,
            'business_id' => $tracking_locations->business_id,
            'last_activity_raw' => $tracking_locations->created_at,
            'last_activity' => $tracking_locations->created_at->format('h:i A, jS F'),
            'last_location_lat' => $location ? $location->lat : null,
            'last_location_lng' => $location ? $location->lng : null,
            'last_location_address' => $location ? $location->address : null,
        ];
    }

    private function getEmployeeDetails($business_member)
    {
        $role = $business_member->role;
        $profile = $business_member->profile();
        return [
            'employee_id' => $business_member->employee_id,
            'employee_name' => $profile->name,
            'employee_role' => $role ? $role->name : null,
            'employee_department' => $role ? $role->businessDepartment->name : null,
            'pro_pic' => $profile->pro_pic
        ];
    }
}
