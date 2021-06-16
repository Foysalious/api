<?php namespace Sheba\Business\ApprovalSetting;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\ApprovalSettingApprover\Types;
use Sheba\Helpers\HasErrorCodeAndMessage;

class FindApprovers
{
    use HasErrorCodeAndMessage;

    /**
     * @var Application|mixed
     */
    private $defaultApprovalSetting;
    /**
     * @var array
     */
    private $managers;
    /**
     * @var array
     */
    private $approvers;
    /**
     * @var array
     */
    private $departments;
    /**
     * @var null
     */
    private $headOfDepartment;

    /**
     * FindApprovers constructor.
     */
    public function __construct()
    {
        $this->defaultApprovalSetting = app(MakeDefaultApprovalSetting::class);
        $this->headOfDepartment = null;
        $this->departments = [];
        $this->approvers = [];
        $this->managers = [];
    }

    /**
     * @param $approval_setting
     * @param $business_member
     * @return array
     */
    public function calculateApprovers($approval_setting, $business_member)
    {
        if (!$approval_setting) {
            $approval_setting_approvers = $this->defaultApprovalSetting->getApprovalSettings()['approvers'];
            foreach ($approval_setting_approvers as $approver) {
                if ($approver['type'] == Types::LM) {
                    /** @var BusinessMember $line_manager */
                    $line_manager = $business_member->manager()->first();
                    if ($line_manager) $this->approvers[$approver['order']] = $line_manager->id;
                }
                if ($approver['type'] == Types::HOD) {
                    $this->getHeadOfDepartment($business_member);
                    if ($this->headOfDepartment) $this->approvers[$approver['order']] = $this->headOfDepartment->id;
                }
                if ($approver['type'] == Types::EMPLOYEE) {
                    $this->approvers[$approver['order']] = (int)$approver['type_id'];
                }
            }
        } else {
            $approval_setting_approvers = $approval_setting->approvers;
            foreach ($approval_setting_approvers as $approver) {
                if ($approver->type == Types::LM) {
                    /** @var BusinessMember $line_manager */
                    $line_manager = $business_member->manager()->first();
                    if ($line_manager) $this->approvers[$approver->order] = $line_manager->id;
                }
                if ($approver->type == Types::HOD) {
                    $this->getHeadOfDepartment($business_member);
                    if ($this->headOfDepartment) $this->approvers[$approver->order] = $this->headOfDepartment->id;
                }

                if ($approver->type == Types::EMPLOYEE) {
                    $this->approvers[$approver->order] = (int)$approver->type_id;
                }
            }
        }
        $this->approvers = array_unique($this->approvers);
        ksort($this->approvers);
        return $this->approvers;
    }

    /**
     * @param $business_member
     */
    private function getHeadOfDepartment($business_member)
    {
        /** @var BusinessMember $manager */
        $manager = $business_member->manager()->first();

        if ($manager) {
            if (in_array($manager->id, $this->managers)) {
                return;
            }
            /** @var BusinessDepartment $business_member_department */
            $business_member_department = $business_member->department();
            /** @var BusinessDepartment $manager_department */
            $manager_department = $manager->department();
            if ($manager_department && ($business_member_department->id == $manager_department->id)) {
                $this->headOfDepartment = $manager;
            }
            array_push($this->managers, $manager->id);
            $this->getHeadOfDepartment($manager);
        }
        return;
    }

    /**
     * @param $approvers
     * @return array
     */
    public function getApproversInfo($approvers)
    {
        $default_approvers = [];
        foreach ($approvers as $approver) {
            $business_member = BusinessMember::find($approver);
            $member = $business_member->member;
            $profile = $member->profile;

            array_push($default_approvers, [
                'name' => $profile->name ? $profile->name : 'n/s',
                'status' => null
            ]);
        }
        return $default_approvers;
    }

    /**
     * @param $approvers
     * @return array
     */
    public function getApproversAllInfo($approvers)
    {
        $default_approvers = [];
        foreach ($approvers as $approver) {
            $business_member = BusinessMember::find($approver);
            $member = $business_member->member;
            $profile = $member->profile;
            $role = $business_member->role;
            array_push($default_approvers, [
                'name' => $profile->name,
                'designation' => $role ? $role->name : null,
                'department' => $role ? $role->businessDepartment->name : null,
                'phone' => $profile->mobile,
                'profile_pic' => $profile->pro_pic,
                'status' => null
            ]);
        }
        return $default_approvers;
    }
}
