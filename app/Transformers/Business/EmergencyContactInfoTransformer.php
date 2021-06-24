<?php namespace App\Transformers\Business;


use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class EmergencyContactInfoTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        $member = $business_member->member;

        return [
            'emergency_name' => $member->emergency_contract_person_name,
            'emergency_number' => $member->emergency_contract_person_number,
            'emergency_person_relationship' => $member->emergency_contract_person_relationship,
        ];
    }

}
