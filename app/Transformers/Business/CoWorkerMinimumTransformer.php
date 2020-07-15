<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\Member;

class CoWorkerMinimumTransformer extends TransformerAbstract
{
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $role = $member->businessMember->role;
        return [
            'id' => $member->businessMember->id,
            'profile' => [
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic
            ],
            'designation' => $role ? $role->name : null
        ];
    }
}
