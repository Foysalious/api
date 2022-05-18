<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class LiveTrackingEmployeeListsTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        $department = $business_member->department();
        $profile = $business_member->profile();
        $designation = $business_member->role()->first();

        return [
            'id' => $business_member->id,
            'employee_id' => $business_member->employee_id,
            'name' => $profile->name,
            'department' => $department ? $department->name : null,
            'designation' => $designation ? $designation->name : null,
            'is_live_track_enable' => $business_member->is_live_track_enable,
        ];
    }
}