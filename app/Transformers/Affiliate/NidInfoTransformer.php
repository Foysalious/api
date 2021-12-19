<?php


namespace Transformers\Affiliate;


use App\Models\Profile;
use Sheba\Gender\Gender;
use League\Fractal\TransformerAbstract;

class NidInfoTransformer extends TransformerAbstract
{
    public function transform(Profile $profile)
    {
        $this->profile = $profile;

        return [
            'name' => $profile->name,
            'bn_name' => $profile->bn_name,
            'nid_no' => $profile->nid_no,
            'dob' => $profile->dob,
            'father_name' => $profile->father_name,
            'mother_name' => $profile->mother_name,
            'blood_group' => $profile->blood_group,
            'address' => $profile->address,
            'gender' => Gender::getGenderDisplayableName($profile->gender)
        ];
    }

}
