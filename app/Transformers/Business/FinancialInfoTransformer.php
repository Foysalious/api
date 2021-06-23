<?php namespace App\Transformers\Business;


use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class FinancialInfoTransformer extends TransformerAbstract
{

    public function transform(BusinessMember $business_member)
    {
        $member = $business_member->member;
        $profile = $member->profile;
        $bank = $profile->banks->last();

        return [
            'bank_name' => $bank ? ucwords(str_replace('_', ' ', $bank->bank_name)) : null,
            'account_no' => $bank ? $bank->account_no : null,
            'tin_no' => $profile->tin_no,
            'tin_certificate_name' => $profile->tin_certificate ? array_last(explode('/', $profile->tin_certificate)) : null,
            'tin_certificate' => $profile->tin_certificate,
        ];
    }

}
