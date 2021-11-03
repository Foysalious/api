<?php namespace App\Transformers\Business;

use Sheba\Dal\BusinessMemberBkashInfo\BusinessMemberBkashInfo;
use League\Fractal\TransformerAbstract;
use App\Models\BusinessMember;
use App\Models\Profile;

class CoWorkerBkashNumberReportTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        /** @var Profile $profile */
        $profile = $business_member->profile();
        /** @var BusinessMemberBkashInfo $bkash_info */
        $bkash_info = $business_member->bkashInfos->last();

        return [
            'id' => $business_member->id,
            'profile' => [
                'name' => $profile->name,
                'email' => $profile->email,
            ],
            'bkash_number' => $bkash_info ? $bkash_info->account_no : null,
        ];
    }
}