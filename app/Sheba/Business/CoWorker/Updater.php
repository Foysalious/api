<?php namespace Sheba\Business\CoWorker;

use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Updater as BusinessMemberUpdater;
use Sheba\Business\Role\Requester as RoleRequester;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Business\Role\Creator as RoleCreator;
use Sheba\Business\Role\Updater as RoleUpdater;
use Sheba\Repositories\ProfileRepository;
use Illuminate\Database\Eloquent\Model;
use Sheba\FileManagers\CdnFileManager;
use App\Repositories\FileRepository;
use Sheba\FileManagers\FileManager;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use App\Models\BusinessRole;
use App\Models\Profile;
use App\Models\Member;
use Carbon\Carbon;
use Throwable;
use DB;

class Updater
{
    use CdnFileManager, FileManager, ModificationFields;

    /** @var FileRepository $fileRepository */
    private $fileRepository;
    /** @var ProfileRepository $profileRepository */
    private $profileRepository;
    /** @var BasicRequest $basicRequest */
    private $basicRequest;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    /** @var Member $member */
    private $member;
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
     */
    public function __construct(FileRepository $file_repository, ProfileRepository $profile_repository,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                RoleRequester $role_requester, RoleCreator $role_creator, RoleUpdater $role_updater,
                                BusinessMemberRequester $business_member_requester, BusinessMemberCreator $business_member_creator,
                                BusinessMemberUpdater $business_member_updater)
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
     * @return mixed
     */
    public function getBusinessMember()
    {
        return $this->businessMember = $this->basicRequest->getBusinessMember();
    }

    /**
     * @return mixed
     */
    public function getMember()
    {
        return $this->member = $this->getBusinessMember()->member;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile = $this->getMember()->profile;
    }

    /**
     * @return null
     */
    public function updateBasicInfo()
    {
        DB::beginTransaction();
        try {
            $this->getProfile();
            $profile_data = [
                'pro_pic' => $this->getProfilePicture($this->profile),
                'name' => $this->basicRequest->getFirstName() . ' ' . $this->basicRequest->getLastName(),
                'email' => $this->basicRequest->getEmail(),
            ];
            $this->profileRepository->update($this->profile, $profile_data);
            $this->businessRole = $this->businessRoleCreate();
            $this->businessMember = $this->businessMemberUpdate();
            DB::commit();
            return $this->businessMember;
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    /**
     * @return Model
     */
    private function businessRoleCreate()
    {
        $business_role_requester = $this->roleRequester->setDepartment($this->basicRequest->getDepartment())
            ->setName($this->basicRequest->getRole())->setIsPublished(1);
        return $this->roleCreator->setRequester($business_role_requester)->create();
    }

    /**
     * @return Model
     */
    private function businessMemberUpdate()
    {
        $business_member_requester = $this->businessMemberRequester->setRole($this->businessRole->id)
            ->setManagerEmployee($this->basicRequest->getManagerEmployee());
        return $this->businessMemberUpdater->setBusinessMember($this->businessMember)->setRequester($business_member_requester)->update();
    }

    /**
     * @param $profile
     * @param string $image_for
     * @return bool|string
     */
    private function getProfilePicture($profile, $image_for = 'pro_pic')
    {
        $photo = $this->basicRequest->getProPic();
        if (basename($profile->$image_for) != 'default.jpg') {
            $filename = substr($profile->{$image_for}, strlen(config('sheba.s3_url')));
            $this->deleteOldImage($filename);
        }

        return $this->fileRepository->uploadToCDN($this->makePicName($profile, $photo, $image_for), $photo, 'images/profiles/' . $image_for . '_');
    }

    /**
     * @param $filename
     */
    private function deleteOldImage($filename)
    {
        $old_image = substr($filename, strlen(config('sheba.s3_url')));
        $this->fileRepository->deleteFileFromCDN($old_image);
    }

    /**
     * @param $profile
     * @param $photo
     * @param string $image_for
     * @return string
     */
    private function makePicName($profile, $photo, $image_for = 'profile')
    {
        return $filename = Carbon::now()->timestamp . '_' . $image_for . '_image_' . $profile->id . '.' . $photo->extension();
    }
}
