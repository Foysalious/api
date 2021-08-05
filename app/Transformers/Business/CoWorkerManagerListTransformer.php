<?php namespace App\Transformers\Business;


use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class CoWorkerManagerListTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        $profile = $business_member->member->profile;
        $role = $business_member->role;
        return [
            'id' => $business_member->id,
            'name' => $profile->name,
            'pro_pic' => $profile->pro_pic,
            'designation' => $role ? $role->name : null,
            'manager_id' => $business_member->manager_id,
        ];
    }

}