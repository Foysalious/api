<?php namespace Sheba\Business\CoWorker;

use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use Sheba\Business\CoWorker\Requests\EmergencyRequest;
use Sheba\Business\CoWorker\Requests\FinancialRequest;
use Sheba\Business\CoWorker\Requests\OfficialRequest;
use Sheba\Business\CoWorker\Requests\PersonalRequest;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Updater as BusinessMemberUpdater;
use Sheba\Business\Role\Requester as RoleRequester;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Business\Role\Creator as RoleCreator;
use Sheba\Business\Role\Updater as RoleUpdater;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileBankInfoInterface;
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
    /** @var CoWorkerRequester $coWorkerRequester */
    private $coWorkerRequester;
    /** @var BasicRequest $basicRequest */
    private $basicRequest;
    /** @var OfficialRequest $officialRequest */
    private $officialRequest;
    /** @var PersonalRequest $personalRequest */
    private $personalRequest;
    /** @var FinancialRequest $financialRequest */
    private $financialRequest;
    /** @var EmergencyRequest $emergencyRequest */
    private $emergencyRequest;
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
    /** ProfileBankInfoInterface $profileBankInfoRepository */
    private $profileBankInfoRepository;
    /** MemberRepositoryInterface $memberRepository */
    private $memberRepository;
    /** @var BusinessRoleRepositoryInterface $businessRoleRepository */
    private $businessRoleRepository;

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
     * @param CoWorkerRequester $coWorker_requester
     * @return $this
     */
    public function setCoWorkerRequest(CoWorkerRequester $coWorker_requester)
    {
        $this->coWorkerRequester = $coWorker_requester;
        return $this;
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
     * @param OfficialRequest $official_request
     * @return $this
     */
    public function setOfficialRequest(OfficialRequest $official_request)
    {
        $this->officialRequest = $official_request;
        return $this;
    }

    /**
     * @param PersonalRequest $personal_request
     * @return $this
     */
    public function setPersonalRequest(PersonalRequest $personal_request)
    {
        $this->personalRequest = $personal_request;
        return $this;
    }

    /**
     * @param FinancialRequest $financial_request
     * @return $this
     */
    public function setFinancialRequest(FinancialRequest $financial_request)
    {
        $this->financialRequest = $financial_request;
        return $this;
    }

    /**
     * @param EmergencyRequest $emergency_request
     * @return $this
     */
    public function setEmergencyRequest(EmergencyRequest $emergency_request)
    {
        $this->emergencyRequest = $emergency_request;
        return $this;
    }

    /**
     * @param $member
     * @return $this
     */
    public function setMember($member)
    {
        $this->member = Member::findOrFail($member);
        $this->businessMember = $this->member->businessMember;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @return mixed
     */
    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile = $this->getMember()->profile;
    }

    /**
     * @return array|null
     */
    public function basicInfoUpdate()
    {
        DB::beginTransaction();
        try {
            $this->getProfile();
            $profile_data = [];
            $profile_pic_name = $profile_pic = null;
            $profile_image = $this->basicRequest->getProPic();

            $profile_data['name'] = $this->basicRequest->getFirstName() . ' ' . $this->basicRequest->getLastName();
            if ($profile_image) {
                $profile_pic_name = $profile_image->getClientOriginalName();
                $profile_pic = $this->getPicture($this->profile, $this->basicRequest->getProPic());
                $profile_data['pro_pic'] = $profile_pic;
            }
            if ($this->basicRequest->getEmail()) $profile_data['email'] = $this->basicRequest->getEmail();
            $this->profileRepository->updateRaw($this->profile, $profile_data);

            $this->businessRole = $this->getBusinessRole();

            $business_member_requester = $this->businessMemberRequester
                ->setRole($this->businessRole->id)
                ->setManagerEmployee($this->basicRequest->getManagerEmployee());

            $this->businessMember = $this->businessMemberUpdater
                ->setBusinessMember($this->businessMember)
                ->setRequester($business_member_requester)
                ->update();

            DB::commit();
            return [$this->businessMember, $profile_pic_name, $profile_pic];
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    private function getBusinessRole()
    {
        $business_role = $this->businessRoleRepository
            ->whereLike('name', $this->basicRequest->getRole())
            ->where('business_department_id', $this->basicRequest->getDepartment())
            ->first();

        if ($business_role) return $business_role;

        return $this->businessRoleCreate();
    }

    /**
     * @return BusinessMember|Model|null
     */
    public function officialInfoUpdate()
    {
        DB::beginTransaction();
        try {
            $business_member_requester = $this->businessMemberRequester->setJoinDate($this->officialRequest->getJoinDate())
                ->setGrade($this->officialRequest->getGrade())
                ->setEmployeeType($this->officialRequest->getEmployeeType())
                ->setPreviousInstitution($this->officialRequest->getPreviousInstitution());
            $this->businessMember = $this->businessMemberUpdater->setBusinessMember($this->businessMember)->setRequester($business_member_requester)->update();
            DB::commit();
            return $this->businessMember;
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    /**
     * @return array|null
     */
    public function personalInfoUpdate()
    {
        DB::beginTransaction();
        try {
            $this->getProfile();
            $nid_image_front_name = $this->personalRequest->getNidFront() ? $this->personalRequest->getNidFront()->getClientOriginalName() : null;
            $nid_image_front = $this->personalRequest->getNidFront() ? $this->getPicture($this->profile, $this->personalRequest->getNidFront(), 'nid_image_front') : null;
            $nid_image_back_name = $this->personalRequest->getNidBack() ? $this->personalRequest->getNidBack()->getClientOriginalName() : null;
            $nid_image_back = $this->personalRequest->getNidBack() ? $this->getPicture($this->profile, $this->personalRequest->getNidBack(), 'nid_image_back') : null;
            $profile_data = [
                'mobile' => $this->personalRequest->getPhone(),
                'address' => $this->personalRequest->getAddress(),
                'nationality' => $this->personalRequest->getNationality(),
                'nid_no' => $this->personalRequest->getNidNumber(),
                'nid_image_front' => $nid_image_front,
                'nid_image_back' => $nid_image_back,
                'dob' => $this->personalRequest->getDateOfBirth()
            ];
            $this->profile = $this->profileRepository->update($this->profile, $profile_data);
            DB::commit();
            return [$this->profile, $nid_image_front_name, $nid_image_front, $nid_image_back_name, $nid_image_back];
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    /**
     * @return array|null
     */
    public function financialInfoUpdate()
    {
        DB::beginTransaction();
        try {
            $this->getProfile();
            $tin_certificate_name = $this->financialRequest->getTinCertificate() ? $this->financialRequest->getTinCertificate()->getClientOriginalName() : null;
            $tin_certificate_link = $this->financialRequest->getTinCertificate() ? $this->getPicture($this->profile, $this->financialRequest->getTinCertificate(), 'tin_certificate') : null;
            $profile_data = [
                'tin_no' => $this->financialRequest->getTinNumber(),
                'tin_certificate' => $tin_certificate_link,
            ];
            $this->profileRepository->update($this->profile, $profile_data);
            $profile_bank_data = [
                'bank_name' => $this->financialRequest->getBankName(),
                'account_no' => $this->financialRequest->getBankAccNumber(),
                'profile_id' => $this->profile->id,
            ];
            $this->profileBankInfoRepository->create($profile_bank_data);
            DB::commit();
            return [$this->profile, $tin_certificate_name, $tin_certificate_link];
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    /**
     * @return Member|null
     */
    public function emergencyInfoUpdate()
    {
        DB::beginTransaction();
        try {
            $this->getMember();
            $member_data = [
                'emergency_contract_person_name' => $this->emergencyRequest->getEmergencyContractPersonName(),
                'emergency_contract_person_number' => $this->emergencyRequest->getEmergencyContractPersonMobile(),
                'emergency_contract_person_relationship' => $this->emergencyRequest->getRelationshipEmergencyContractPerson(),
            ];
            $this->memberRepository->update($this->member, $member_data);
            DB::commit();
            return $this->member;
        } catch (Throwable $e) {
            DB::rollback();
            return null;
        }
    }

    /**
     * @return BusinessMember|Model|null
     */
    public function statusUpdate()
    {
        DB::beginTransaction();
        try {
            $business_member_requester = $this->businessMemberRequester->setStatus($this->coWorkerRequester->getStatus());
            $this->businessMember = $this->businessMemberUpdater->setBusinessMember($this->businessMember)->setRequester($business_member_requester)->update();
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
        $business_role_requester = $this->roleRequester
            ->setDepartment($this->basicRequest->getDepartment())
            ->setName($this->basicRequest->getRole())
            ->setIsPublished(1);

        return $this->roleCreator->setRequester($business_role_requester)->create();
    }

    /**
     * @param $profile
     * @param $photo
     * @param string $image_for
     * @return bool|string
     */
    private function getPicture($profile, $photo, $image_for = 'pro_pic')
    {
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
