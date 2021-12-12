<?php namespace App\Transformers\Business;


use App\Models\BusinessMember;
use App\Sheba\Business\CoWorker\ProfileInformation\SocialLink;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PersonalInfoTransformer extends TransformerAbstract
{

    public function transform(BusinessMember $business_member)
    {
        $member = $business_member->member;
        $profile = $member->profile;

        return [
            'gender' => $profile->gender,
            'id' => $profile->id,
            'mobile' => $business_member->mobile,
            'profile_picture' => $profile->pro_pic,
            'dob' => $profile->dob ? Carbon::parse($profile->dob)->format('d F, Y') : null,
            'address' => $profile->address,
            'nationality' => $profile->nationality,
            'nid_no' => $profile->nid_no,
            'nid_front_image' => $profile->nid_image_front,
            'nid_back_image' => $profile->nid_image_back,
            'passport_no' => $profile->passport_no,
            'passport_image' => $profile->passport_image,
            'blood_group' => !empty($profile->blood_group) ? $profile->blood_group : null,
            'social_links' => (new SocialLink($member))->get()
        ];
    }
}
