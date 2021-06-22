<?php namespace App\Sheba\Business\CoWorker\Information\Financial;


use App\Models\BusinessMember;

class GetFinancialInfo
{
    /*** @var BusinessMember */
    private $businessMember;

    public function __construct(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
    }

    public function get()
    {
        $member = $this->businessMember->member;
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
