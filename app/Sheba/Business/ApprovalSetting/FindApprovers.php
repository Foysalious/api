<?php namespace Sheba\Business\ApprovalSetting;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use Illuminate\Foundation\Application;
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
                    if (!$line_manager) $this->setError(422, 'Manager not set yet!');
                    $this->approvers[$approver->order] = $line_manager->id;
                }
                if ($approver['type'] == Types::HOD) {
                    /** @var BusinessDepartment $department */
                    $department = $business_member->department();
                    if (!$department) $this->setError(422, 'Department not set yet!');
                    $this->getHeadOfDepartment($business_member);
                    if ($this->headOfDepartment) $this->approvers[$approver->order] = $this->headOfDepartment->id;
                }

                if ($approver->type == Types::EMPLOYEE) {
                    $this->approvers[$approver->order] = (int)$approver->type_id;
                }
            }
        } else {
            $approval_setting_approvers = $approval_setting->approvers;
            foreach ($approval_setting_approvers as $approver) {
                if ($approver->type == Types::LM) {
                    /** @var BusinessMember $line_manager */
                    $line_manager = $business_member->manager()->first();
                    if (!$line_manager) $this->setError(422, 'Manager not set yet!');
                    $this->approvers[$approver->order] = $line_manager->id;
                }
                if ($approver->type == Types::HOD) {
                    /** @var BusinessDepartment $department */
                    $department = $business_member->department();
                    if (!$department) $this->setError(422, 'Department not set yet!');
                    $this->getHeadOfDepartment($business_member);
                    if ($this->headOfDepartment) $this->approvers[$approver->order] = $this->headOfDepartment->id;
                }

                if ($approver->type == Types::EMPLOYEE) {
                    $this->approvers[$approver->order] = (int)$approver->type_id;
                }
            }
        }
        ksort($this->approvers);
        return $this->approvers;
    }

    /**
     * @param $business_member
     */
    public function getHeadOfDepartment($business_member)
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
            if ($business_member_department->id == $manager_department->id) {
                $this->headOfDepartment = $manager;
            }
            array_push($this->managers, $manager->id);
            $this->getHeadOfDepartment($manager);
        }
        return;
    }
}