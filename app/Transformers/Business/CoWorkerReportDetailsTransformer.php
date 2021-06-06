<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use App\Models\Member;

class CoWorkerReportDetailsTransformer extends TransformerAbstract
{
    private $isInactiveFilterApplied;

    public function __construct($is_inactive_filter_applied)
    {
        $this->isInactiveFilterApplied = $is_inactive_filter_applied;
    }

    /**
     * @param Member $member
     * @return array
     */
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $profile_bank_info = $profile->banks->last();

        $business_member = ($this->isInactiveFilterApplied) ? $member->businessMemberGenerated : $member->businessMember;

        $bank_name = $profile_bank_info ? ucwords(str_replace('_', ' ', $profile_bank_info->bank_name)) : null;
        $account_no = $profile_bank_info ? $profile_bank_info->account_no : null;

        $role = $business_member->role;

        return [
            'id' => $member->id,
            'employee_id' => $business_member->employee_id,
            'profile' => [
                'name' => $profile->name,
                'mobile' => $profile->mobile,
                'email' => $profile->email,
            ],
            'status' => $business_member->status,
            'department' => $role ? $role->businessDepartment->name : '-',
            'designation' => $role ? $role->name : '-',
            'manager_name' => $business_member->manager_id ? $this->getManagerName($business_member->manager_id) : '-',
            'join_date' => Carbon::parse($business_member->join_date)->format('jS M, Y'),
            'employee_grade' => $business_member->grade,
            'employee_type' => $business_member->employee_type,
            'previous_institution' => $business_member->previous_institution,
            'date_of_birth' => $profile->dob,
            'address' => $profile->address,
            'nationality' => $profile->nationality,
            'nid_no' => $profile->nid_no,
            'tin_no' => $profile->tin_no,
            'bank_name' => $bank_name,
            'bank_account_no' => $account_no,
            'emergency_contract_person_name' => $member->emergency_contract_person_name,
            'emergency_contract_person_number' => $member->emergency_contract_person_number,
            'emergency_contract_person_relationship' => $member->emergency_contract_person_relationship
        ];
    }

    /**
     * @param $manager_id
     * @return mixed
     */
    private function getManagerName($manager_id)
    {
        return BusinessMember::findOrFail($manager_id)->member->profile->name;
    }
}
