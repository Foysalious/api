<?php namespace Sheba\Business\CoWorker;

use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Updater as BusinessMemberUpdater;
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
     */
    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                RoleRequester $role_requester, RoleCreator $role_creator, RoleUpdater $role_updater,
                                BusinessMemberRequester $business_member_requester, BusinessMemberCreator $business_member_creator,
                                BusinessMemberUpdater $business_member_updater, ProfileBankInfoInterface $profile_bank_information,
                                MemberRepositoryInterface $member_repository)
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

    public function basicInfoStore()
    {
        DB::beginTransaction();
        try {
            $profile = $this->profileRepository->checkExistingEmail($this->basicRequest->getEmail());
            $this->businessRoleCreate();
            $new_member = null;
            if (!$profile) {
                $profile = $this->createProfile();
                $new_member = $this->createMember($profile);
                $this->businessMember = $this->createBusinessMember($this->business, $new_member);
            } else {
                $old_member = $profile->member;

                if ($old_member) {
                    if ($old_member->businesses()->where('businesses.id', $this->business->id)->count() > 0) {
                        $this->setError(409, "This person is already added.");
                    }
                    if ($old_member->businesses()->where('businesses.id', '<>', $this->business->id)->count() > 0) {
                        $this->setError(422, "This person is already connected with another business.");
                    }
                    $new_member = $old_member;
                } else {
                    $new_member = $this->createMember($profile);
                }
                $this->sendExistingUserMail($profile);
                $this->businessMember = $this->createBusinessMember($this->business, $new_member);
            }
            DB::commit();
            return $new_member;
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    /**
     * @param $business
     * @param $co_member
     * @return Model
     */
    private function createBusinessMember($business, $co_member)
    {
        $business_member_requester = $this->businessMemberRequester->setBusinessId($business->id)
            ->setMemberId($co_member->first()->id)
            ->setRole($this->businessRole->id)
            ->setManagerEmployee($this->basicRequest->getManagerEmployee());
        return $this->businessMemberCreator->setRequester($business_member_requester)->create();
    }

    /**
     * @return Profile
     */
    private function createProfile()
    {
        $password = str_random(6);
        $data = [
            '_token' => str_random(255),
            'name' => $this->basicRequest->getFirstName() . ' ' . $this->basicRequest->getLastName(),
            'email' => $this->basicRequest->getEmail(),
            'password' => bcrypt($password),
            'pro_pic' => $this->profileRepository->saveProPic($this->basicRequest->getProPic(), $this->basicRequest->getProPic()->getClientOriginalName()),
        ];
        $profile = $this->profileRepository->store($data);
        dispatch((new SendBusinessRequestEmail($this->basicRequest->getEmail()))->setPassword($password)->setTemplate('emails.co-worker-invitation'));
        return $profile;
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

    private function businessRoleCreate()
    {
        $business_role_requester = $this->roleRequester->setDepartment($this->basicRequest->getDepartment())
            ->setName($this->basicRequest->getRole())->setIsPublished(1);
        $this->businessRole = $this->roleCreator->setRequester($business_role_requester)->create();
    }

    /**
     * @param $profile
     */
    private function sendExistingUserMail($profile)
    {
        $CMail = new SendBusinessRequestEmail($profile->email);
        if (empty($profile->password)) {
            $profile->password = str_random(6);
            $CMail->setPassword($profile->password);
            $profile->save();
        }
        $CMail->setTemplate('emails.co-worker-invitation');
        dispatch($CMail);
    }
}
