<?php namespace Sheba\Business\CoWorker;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Sheba\Business\Role\Creator as RoleCreator;
use Sheba\Business\Role\Requester as RoleRequester;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class UpdaterV2
{
    use HasErrorCodeAndMessage, ModificationFields;

    private $name;
    private $mobile;
    private $department;
    private $designation;
    private $manager;
    /** @var ProfileRepositoryInterface $profileRepository */
    private $profileRepository;
    private $businessMember;
    /** @var Profile $profile */
    private $profile;
    private $businessRole;
    /** @var RoleRequester $roleRequester */
    private $roleRequester;
    /** @var RoleCreator $roleCreator */
    private $roleCreator;
    /** @var BusinessRoleRepositoryInterface $businessRoleRepository */
    private $businessRoleRepository;
    private $businessMemberUpdater;
    private $businessMemberRepository;
    private $status;
    private $email;

    /**
     * UpdaterV2 constructor.
     * @param ProfileRepositoryInterface $profile_repository
     * @param BusinessRoleRepositoryInterface $business_role_repository
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param RoleRequester $role_requester
     * @param RoleCreator $role_creator
     */
    public function __construct(ProfileRepositoryInterface $profile_repository,
                                BusinessRoleRepositoryInterface $business_role_repository,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                RoleRequester $role_requester, RoleCreator $role_creator)
    {
        $this->profileRepository = $profile_repository;
        $this->roleRequester = $role_requester;
        $this->roleCreator = $role_creator;
        $this->businessRoleRepository = $business_role_repository;
        $this->businessMemberRepository = $business_member_repository;
    }

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        /** @var Member $member */
        $member = $this->businessMember->member;
        $this->profile = $member->profile;

        return $this;
    }

    /**
     * @param mixed $name
     * @return UpdaterV2
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $mobile
     * @return UpdaterV2
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile ? formatMobile($mobile) : null;
        $this->checkMobileUsedWithAnotherBusinessMember();
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        $this->checkEmailUsedWithAnotherBusinessMember();
    }

    /**
     * @param mixed $department
     * @return UpdaterV2
     */
    public function setDepartment($department)
    {
        $this->department = (int)$department;
        return $this;
    }

    /**
     * @param mixed $designation
     * @return UpdaterV2
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;
        if ($this->designation) $this->businessRole = $this->getBusinessRole();

        return $this;
    }

    private function getBusinessRole()
    {
        if (!$this->department) {
            $department = $this->businessMember->department();
            if (!$department) {
                $this->setError(404, 'Please update your department first.');
                return $this;
            }
            $this->department = $department->id;
        }
        $business_role = $this->businessRoleRepository
            ->where('name', $this->designation)
            ->where('business_department_id', $this->department)
            ->first();

        if ($business_role) return $business_role;

        return $this->businessRoleCreate();
    }

    /**
     * @return Model
     */
    private function businessRoleCreate()
    {
        $business_role_requester = $this->roleRequester->setDepartment($this->department)->setName($this->designation)->setIsPublished(1);
        return $this->roleCreator->setRequester($business_role_requester)->create();
    }

    /**
     * @param $manager
     * @return $this
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
        return $this;
    }

    /**
     * @return $this
     */
    private function checkMobileUsedWithAnotherBusinessMember()
    {
        $business_member = $this->businessMemberRepository->checkExistingMobile($this->mobile);
        if (!$business_member) return $this;
        if ($business_member->id != $this->businessMember->id)
            $this->setError(400, 'This mobile number belongs to another member. Please contact with sheba');

        return $this;
    }

    private function checkEmailUsedWithAnotherBusinessMember()
    {
        $profile = $this->profileRepository->checkExistingProfile(null, $this->email);
        if (!$profile) return $this;
        $member = $profile->member;
        if (!$member) {
            $this->setError(400, 'No member has been created yet. Please contact with sheba');
            return $this;
        }

        if ($member->business_member->id != $this->businessMember->id)
            $this->setError(400, 'This email belongs to another member. Please contact with sheba');

        return $this;
    }

    /**
     * @param mixed $status
     * @return UpdaterV2
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function update()
    {
        $profile_data = ['name' => $this->name];
        if ($this->email) $profile_data = array_merge($profile_data, ['email' => $this->email]);
        $this->profileRepository->updateRaw($this->profile, $profile_data);
        if (!$this->manager) $this->manager = $this->businessMember->manager_id;
        $business_member_data = [
            'manager_id' => $this->manager,
            'status' => $this->status ?: $this->businessMember->status,
        ];
        if ($this->mobile) $business_member_data['mobile'] = $this->mobile;
        if ($this->businessRole) $business_member_data['business_role_id'] = $this->businessRole->id;

        $this->businessMemberRepository->update($this->businessMember, $this->withUpdateModificationField($business_member_data));
    }
}
