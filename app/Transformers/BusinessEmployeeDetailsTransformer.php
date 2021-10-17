<?php namespace App\Transformers;

use App\Models\Member;
use App\Sheba\Business\CoWorker\ProfileInformation\SocialLink;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class BusinessEmployeeDetailsTransformer extends TransformerAbstract
{
    /**
     * @param Member $member
     * @return array
     */
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $business_member = $member->businessMember;
        $role = $business_member->role;

        return [
            'name' => $profile->name ?: null,
            'mobile' => $business_member->mobile,
            'email' => $profile->email,
            'image' => $profile->pro_pic,
            'designation' => $role ? $role->name : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'blood_group' => $profile->blood_group,
            'dob' => $profile->dob ? Carbon::parse($profile->dob)->format('jS F') : null,
            'social_link' => (new SocialLink($member))->get()
        ];
    }
}
