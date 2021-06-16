<?php namespace Sheba\Business\CoWorker;

use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Updater as BusinessMemberUpdater;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileBankInfoInterface;
use Sheba\Business\Role\Requester as RoleRequester;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Business\Role\Creator as RoleCreator;
use Sheba\Business\Role\Updater as RoleUpdater;
use Sheba\Repositories\ProfileRepository;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\SendBusinessRequestEmail;
use Sheba\FileManagers\CdnFileManager;
use App\Repositories\FileRepository;
use Sheba\FileManagers\FileManager;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use App\Models\BusinessRole;
use App\Models\Business;
use App\Models\Profile;
use App\Models\Member;
use Throwable;
use DB;

class Creator
{
    use HasErrorCodeAndMessage, CdnFileManager, FileManager, ModificationFields;

    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var CoWorkerRequester $coWorkerRequester */
    private $coWorkerRequester;
    /** @var BasicRequest $basicRequest */
    private $basicRequest;
    /** @var Business $business */
    private $business;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    /** @var Member $managerMember */
    private $managerMember;
    /** @var Profile $profile */
    private $profile;
    /** @var BusinessRole $businessRole */
    private $businessRole;
    /** BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** RoleRequester $roleRequester */
    private $roleRequester;
    /** RoleCreator $roleCreator */
    private $roleCreator;
    /** RoleUpdater $roleUpdater */
    private $roleUpdater;
    /** BusinessMemberRequester $businessMemberRequester */
    private $businessMemberRequester;
    /** BusinessMemberCreator $businessMemberCreator */
    private $businessMemberCreator;
    /** BusinessMemberUpdater $businessMemberUpdater */
    private $businessMemberUpdater;
    /** ProfileBankInfoInterface $profileBankInfoRepository */
    private $profileBankInfoRepository;
    /** MemberRepositoryInterface $memberRepository */
    private $memberRepository;
    /** @var BusinessRoleRepositoryInterface $businessRoleRepository */
    private $businessRoleRepository;
    private $email;
    private $status;
    /** @var string $password */
    private $password;

    /**
     * Updater constructor.
     * @param FileRepository $file_repository
     * @param ProfileRepository $profile_repository
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param RoleRequester $role_requester
     * @param RoleCreator $role_creator
     * @param RoleUpdater $role_updater
     * @param BusinessMemberRequester $business_member_requester
     * @param BusinessMemberCreator $business_member_creator
     * @param BusinessMemberUpdater $business_member_updater
     * @param ProfileBankInfoInterface $profile_bank_information
     * @param MemberRepositoryInterface $member_repository
     * @param BusinessRoleRepositoryInterface $business_role_repository
     */
    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                RoleRequester $role_requester, RoleCreator $role_creator, RoleUpdater $role_updater,
                                BusinessMemberRequester $business_member_requester, BusinessMemberCreator $business_member_creator,
                                BusinessMemberUpdater $business_member_updater, ProfileBankInfoInterface $profile_bank_information,
                                MemberRepositoryInterface $member_repository, BusinessRoleRepositoryInterface $business_role_repository)
    {
        $this->fileRepository = $file_repository;
        $this->profileRepository = $profile_repository;
        $this->businessMemberRepository = $business_member_repository;
        $this->roleRequester = $role_requester;
        $this->roleCreator = $role_creator;
        $this->roleUpdater = $role_updater;
        $this->businessMemberRequester = $business_member_requester;
        $this->businessMemberCreator = $business_member_creator;
        $this->businessMemberUpdater = $business_member_updater;
        $this->profileBankInfoRepository = $profile_bank_information;
        $this->memberRepository = $member_repository;
        $this->businessRoleRepository = $business_role_repository;
    }

    /**
     * @param BasicRequest $basic_request
     * @return $this
     */
    public function setBasicRequest(BasicRequest $basic_request)
    {
        $this->basicRequest = $basic_request;
        return $this;
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param Member $manager_member
     * @return $this
     */
    public function setManagerMember(Member $manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        $this->checkEmailUsedWithAnotherProfile();
        return $this;
    }

    /**
     * @return $this
     */
    private function checkEmailUsedWithAnotherProfile()
    {
        $profile = $this->profileRepository->checkExistingEmail($this->email);
        if (!$profile) return $this;
        if (!$profile->member) return $this;
        if ($profile->member->businesses()->where('businesses.id', $this->business->id)->count() > 0) {
            $this->setError(409, "This person is already added");
        }
        if ($profile->member->businesses()->where('businesses.id', '<>', $this->business->id)->count() > 0) {
            $this->setError(422, "This person is already added with another business");
        }

        return $this;
    }

    public function basicInfoStore()
    {
        $profile = $this->profileRepository->checkExistingEmail($this->basicRequest->getEmail());
        if ($this->basicRequest->getRole()) $this->businessRole = $this->getBusinessRole();
        $member = null;
        if (!$profile) {
            $profile = $this->createProfile();
            $member = $this->createMember($profile);
            $this->businessMember = $this->createBusinessMember($this->business, $member);
        } else {
            $member = $profile->member;
            if (!$member) {
                $member = $this->createMember($profile);
                $this->businessMember = $this->createBusinessMember($this->business, $member);
            }
        }
        $this->sendExistingUserMail($profile);
        return $member;
    }

    /**
     * @param $business
     * @param $member
     * @return Model
     */
    private function createBusinessMember($business, $member)
    {
        $business_role_id = $this->businessRole ? $this->businessRole->id : null;
        $status = $this->status ?: Statuses::ACTIVE;
        $business_member_requester = $this->businessMemberRequester->setBusinessId($business->id)
            ->setMemberId($member->id)
            ->setRole($business_role_id)
            ->setStatus($status)
            ->setManagerEmployee($this->basicRequest->getManagerEmployee());

        return $this->businessMemberCreator->setRequester($business_member_requester)->create();
    }

    /**
     * @return Profile
     */
    private function createProfile()
    {
        $this->password = str_random(6);
        $default_image = 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg';
        $data = [
            '_token' => str_random(255),
            'name' => $this->basicRequest->getFirstName() . ' ' . $this->basicRequest->getLastName(),
            'email' => $this->basicRequest->getEmail(),
            'password' => $this->password,
            'pro_pic' => $this->basicRequest->getProPic() ? $this->profileRepository->saveProPic($this->basicRequest->getProPic(), $this->basicRequest->getProPic()->getClientOriginalName()) : $default_image,
        ];

        return $this->profileRepository->store($data);
    }

    /**
     * @param $profile
     * @return Model
     */
    private function createMember($profile)
    {
        return $this->memberRepository->create([
            'profile_id' => $profile->id,
            'remember_token' => str_random(255)
        ]);
    }

    private function getBusinessRole()
    {
        $business_role = $this->businessRoleRepository
            ->where('name', $this->basicRequest->getRole())
            ->where('business_department_id', $this->basicRequest->getDepartment())
            ->first();
        if ($business_role) return $business_role;
        return $this->businessRoleCreate();
    }

    /**
     * @return Model
     */
    private function businessRoleCreate()
    {
        $business_role_requester = $this->roleRequester
            ->setDepartment($this->basicRequest->getDepartment())
            ->setName($this->basicRequest->getRole())
            ->setIsPublished(1);

        return $this->roleCreator->setRequester($business_role_requester)->create();
    }

    /**
     * @param mixed $status
     * @return Creator
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $profile
     */
    private function sendExistingUserMail($profile)
    {
        try {
            config()->set('services.mailgun.domain', config('services.mailgun.business_domain'));
            $coworker_invite_email = new SendBusinessRequestEmail($profile->email);
            if ($this->password) $coworker_invite_email->setPassword($this->password);
            if (empty($profile->password)) {
                $password = str_random(6);
                $this->profileRepository->updateRaw($profile, ['password' => bcrypt($password)]);
                $coworker_invite_email->setPassword($password);
            }

            $coworker_invite_email->setSubject("Invitation from your co-worker to join digiGO")->setTemplate('emails.co-worker-invitation-v3');
            dispatch($coworker_invite_email);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
        }
    }

    public function resetError()
    {
        return $this->errorCode = null;
    }
}
