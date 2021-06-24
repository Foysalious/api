<?php namespace App\Transformers;

use App\Models\Member;
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
            'name'          => $profile->name ? : null,
            'mobile'        => $business_member->mobile,
            'email'         => $profile->email,
            'image'         => $profile->pro_pic,
            'designation'   => $role ? $role->name : null,
            'department'    => $role ? $role->businessDepartment->name : null,
            'blood_group'   => $profile->blood_group,
            'dob'           => Carbon::parse($profile->dob)->format('jS F'),
            'social_link'   => $this->getSocialLinks($member)
        ];
    }

    private function getSocialLinks($member)
    {
        $social_links = json_decode($member->social_links, 1);
        if (!$social_links) return null;
        $data = [];
        foreach ($social_links as $type => $link){
            array_push($data, [
               'link' =>  $link,
               'type' => $type
            ]);
        }
        return $data;
    }
}
