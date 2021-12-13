<?php namespace App\Transformers\Affiliate;

use App\Models\Profile;
use Sheba\Gender\Gender;
use League\Fractal\TransformerAbstract;

class ProfileDetailTransformer extends TransformerAbstract
{
    public function transform(Profile $profile)
    {
        $this->profile = $profile;
        $is_verified = $profile->affiliate->verification_status == "verified";
        $personal_info = [
            'name' => $profile->name,
            'bn_name' => $profile->bn_name,
            'profile_image' => $profile->pro_pic,
            'nid_no' => $is_verified ? "" : $profile->nid_no,
            'dob' => $is_verified ? "" : $profile->dob,
            'father_name' => $profile->father_name,
            'mother_name' => $profile->mother_name,
            'blood_group' => $profile->blood_group,
            'address' => $profile->address,
            'permanent_address' => $profile->permanent_address,
            'post_office' => $profile->post_office,
            'post_code' => $profile->post_code,
            'gender' => Gender::getGenderDisplayableName($profile->gender)
        ];

        $national_id_card = [
            'front_image' => $is_verified ? "" : $profile->nid_image_front,
            'back_image' => $is_verified ? "" : $profile->nid_image_back
        ];

        return [
            'personal_info'     => $personal_info,
            'financial_info'    => $this->getFinancialInfo($profile),
            'national_id_card'  => $national_id_card
        ];
    }

    private function getFinancialInfo($profile)
    {
        $general_banks = [];
        $mobile_banks = [];
        $is_verified = $profile->affiliate->verification_status == "verified";
        if(!$is_verified) {
            $profile->banks->each(function ($bank) use (&$general_banks) {
                $general_banks[] = [
                    'id' => $bank->id,
                    'bank_name' => $bank->bank_name,
                    'account_no' => $bank->account_no,
                    'branch_name' => $bank->branch_name,
                ];
            });

            $profile->mobileBanks->each(function ($mobileBanks) use (&$mobile_banks) {
                $mobile_banks[] = [
                    'id' => $mobileBanks->id,
                    'bank_name' => $mobileBanks->bank_name,
                    'account_no' => $mobileBanks->account_no,
                ];
            });
        }
        return [
            'general_banking' => ($general_banks) ? $general_banks : null,
            'mobile_banking' => ($mobile_banks) ? $mobile_banks : null
        ];
    }
}
