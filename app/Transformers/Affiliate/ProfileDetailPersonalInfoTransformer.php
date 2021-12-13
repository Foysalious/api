<?php namespace App\Transformers\Affiliate;

use App\Models\Profile;
use Sheba\Gender\Gender;
use League\Fractal\TransformerAbstract;

class ProfileDetailPersonalInfoTransformer extends TransformerAbstract
{
    public function transform(Profile $profile)
    {
        return [
            'name' => $profile->name,
            'bn_name' => $profile->bn_name,
            'profile_image' => $profile->pro_pic,
            'remember_token' => $profile->remember_token,
            'nid_no' => $profile->nid_no,
            'dob' => $profile->dob,
            'father_name' => $profile->father_name,
            'mother_name' => $profile->mother_name,
            'blood_group' => $profile->blood_group,
            'address' => $profile->address,
            'permanent_address' => $profile->permanent_address,
            'post_office' => $profile->post_office,
            'post_code' => $profile->post_code,
            'gender' => Gender::getGenderDisplayableName($profile->gender)
        ];
    }
}
