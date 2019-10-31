<?php namespace App\Transformers\Affiliate;

use App\Models\Profile;
use League\Fractal\TransformerAbstract;

class ProfileDetailTransformer extends TransformerAbstract
{
    public function transform(Profile $profile)
    {
        $this->profile = $profile;

        $personal_info = [
            'name' => $profile->name,
            'bn_name' => $profile->bn_name,
            'profile_image' => $profile->pro_pic,
            'remember_token' => $profile->remember_token,
            'nid_no' => $profile->nid_no,
            'date_of_birth' => $profile->dob,
            'father_name' => $profile->father_name,
            'mother_name' => $profile->mother_name,
            'blood_group' => $profile->blood_group,
            'present_address' => $profile->address,
            'permanent_address' => $profile->permanent_address,
            'post_office' => $profile->post_office,
            'post_code' => $profile->post_code
        ];
        $national_id_card = [
            'front_image' => $profile->nid_image_front,
            'back_image' => $profile->nid_image_back
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
        $profile->banks->each(function ($bank) use (&$general_banks) {
            $general_banks[] = [
                'bank_name' => $bank->bank_name,
                'account_no' => $bank->account_no,
                'branch_name' => $bank->branch_name,
            ];
        });

        $profile->mobileBanks->each(function ($mobileBanks) use (&$mobile_banks) {
            $mobile_banks[] = [
                'bank_name' => $mobileBanks->bank_name,
                'account_no' => $mobileBanks->account_no,
            ];
        });
        return [
            'general_banking' => ($general_banks) ? $general_banks : null,
            'mobile_banking' => ($mobile_banks) ? $mobile_banks : null
        ];
    }
}
