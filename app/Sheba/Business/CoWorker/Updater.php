<?php namespace Sheba\Business\CoWorker;

use App\Models\Business;
use Sheba\Business\BusinessMember\Requester as BusinessMemberRequester;
use Sheba\Business\CoWorker\Requests\Requester as CoWorkerRequester;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\BusinessMember\Creator as BusinessMemberCreator;
use Sheba\Business\BusinessMember\Updater as BusinessMemberUpdater;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileBankInfoInterface;
use Sheba\Business\CoWorker\Requests\EmergencyRequest;
use Sheba\Business\CoWorker\Requests\FinancialRequest;
use Sheba\Business\CoWorker\Requests\OfficialRequest;
use Sheba\Business\CoWorker\Requests\PersonalRequest;
use Sheba\Business\Role\Requester as RoleRequester;
use Sheba\Business\CoWorker\Requests\BasicRequest;
use Sheba\Business\Role\Updater as RoleUpdater;
use Sheba\Business\Role\Creator as RoleCreator;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Repositories\ProfileRepository;
use Illuminate\Database\Eloquent\Model;
use Sheba\FileManagers\CdnFileManager;
use App\Repositories\FileRepository;
use Sheba\FileManagers\FileManager;
use Illuminate\Http\UploadedFile;
use App\Models\BusinessMember;
use Intervention\Image\Image;
use Sheba\ModificationFields;
use App\Models\BusinessRole;
use App\Models\Profile;
use App\Models\Member;
use Carbon\Carbon;
use Throwable;
use DB;

class Updater
{
    use CdnFileManager, FileManager, ModificationFields, HasErrorCodeAndMessage;

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
    private $mobile;
    private $email;
    /** @var Business $business */
    private $business;

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
        $this->profile = $this->member->profile;
        $this->businessMember = $this->member->businessMember;

        if (!$this->businessMember) {
            $this->businessMember = $this->businessMemberRepository->builder()
                ->where('business_id', $this->business->id)
                ->where('member_id', $this->member->id)
                ->where('status', Statuses::INACTIVE)
                ->first();
        }

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
     * @param $image
     * @return bool
     */
    private function isFile($image)
    {
        if ($image instanceof Image || $image instanceof UploadedFile) return true;
        return false;
    }

    private function getBusinessRole()
    {
        $business_role = $this->businessRoleRepository
            ->where(DB::raw('BINARY `name`'), $this->basicRequest->getRole())
            ->where('business_department_id', $this->basicRequest->getDepartment())
            ->first();
        
        if ($business_role) return $business_role;
        return $this->businessRoleCreate();
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

            if ($profile_image != 'null') {
                $profile_pic_name = $this->isFile($profile_image) ? $profile_image->getClientOriginalName() : array_last(explode('/', $profile_image));
                $profile_pic = $this->isFile($profile_image) ? $this->getPicture($this->profile, $profile_image) : $profile_image;
            }
            $profile_data['email'] = ($this->basicRequest->getEmail() == 'null') ? null : $this->basicRequest->getEmail();
            $profile_data['name'] = ($this->basicRequest->getFirstName() == 'null') ? null : $this->basicRequest->getFirstName();
            $profile_data['pro_pic'] = ($profile_image == 'null') ? null : $profile_pic;

            $this->profileRepository->update($this->profile, $profile_data);

            $this->businessRole = $this->getBusinessRole();
            $business_member_data = [
                'business_role_id' => $this->businessRole->id,
                'manager_id' => $this->basicRequest->getManagerEmployee(),
            ];
            $this->businessMember = $this->businessMemberUpdater->setBusinessMember($this->businessMember)->update($business_member_data);

            DB::commit();
            return [$this->businessMember, $profile_pic_name, $profile_pic];
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
            return null;
        }
    }

    /**
     * @return BusinessMember|Model|null
     */
    public function officialInfoUpdate()
    {
        DB::beginTransaction();
        try {
            $business_member_data = [];
            if ($this->officialRequest->getEmployeeId() == 'null') {
                $business_member_data['employee_id'] = null;
            } else {
                $business_member_data['employee_id'] = $this->officialRequest->getEmployeeId();
            }
            if ($this->officialRequest->getJoinDate() == 'null') {
                $business_member_data['join_date'] = null;
            } else {
                $business_member_data['join_date'] = $this->officialRequest->getJoinDate();
            }

            if ($this->officialRequest->getGrade() == 'null') {
                $business_member_data['grade'] = null;
            } else {
                $business_member_data['grade'] = $this->officialRequest->getGrade();
            }
            if ($this->officialRequest->getEmployeeType() == 'null') {
                $business_member_data['employee_type'] = null;
            } else {
                $business_member_data['employee_type'] = $this->officialRequest->getEmployeeType();
            }
            if ($this->officialRequest->getPreviousInstitution() == 'null') {
                $business_member_data['previous_institution'] = null;
            } else {
                $business_member_data['previous_institution'] = $this->officialRequest->getPreviousInstitution();
            }
            $this->businessMember = $this->businessMemberUpdater
                ->setBusinessMember($this->businessMember)
                ->update($business_member_data);
            DB::commit();
            return $this->businessMember;
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
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

            $nid_image_front_name = $nid_image_front = $nid_image_back_name = $nid_image_back = null;
            $nid_front = $this->personalRequest->getNidFront();
            $nid_back = $this->personalRequest->getNidBack();
            if ($nid_front != 'null') {
                $nid_image_front_name = $this->isFile($nid_front) ? $nid_front->getClientOriginalName() : array_last(explode('/', $nid_front));
                $nid_image_front = $this->isFile($nid_front) ? $this->getPicture($this->profile, $nid_front, 'nid_image_front') : $nid_front;
            }
            if ($nid_back != 'null') {
                $nid_image_back_name = $this->isFile($nid_back) ? $nid_back->getClientOriginalName() : array_last(explode('/', $nid_back));
                $nid_image_back = $this->isFile($nid_back) ? $this->getPicture($this->profile, $nid_back, 'nid_image_back') : $nid_back;
            }

            $profile_data = [];
            $business_member_data = [];
            $profile_data['address'] = ($this->personalRequest->getAddress() == 'null') ? null : $this->personalRequest->getAddress();
            $profile_data['nationality'] = ($this->personalRequest->getNationality() == 'null') ? null : $this->personalRequest->getNationality();
            $profile_data['nid_no'] = ($this->personalRequest->getNidNumber() == 'null') ? null : $this->personalRequest->getNidNumber();
            $profile_data['nid_image_front'] = ($nid_front == 'null') ? null : $nid_image_front;
            $profile_data['nid_image_back'] = ($nid_back == 'null') ? null : $nid_image_back;
            $profile_data['dob'] = ($this->personalRequest->getDateOfBirth() == 'null') ? null : $this->personalRequest->getDateOfBirth();

            $this->profile = $this->profileRepository->update($this->profile, $profile_data);

            $business_member_data['mobile'] = ($this->personalRequest->getPhone() == 'null') ? null : $this->personalRequest->getPhone();
            $this->businessMember = $this->businessMemberUpdater
                ->setBusinessMember($this->businessMember)
                ->update($business_member_data);
            DB::commit();
            return [$this->profile, $nid_image_front_name, $nid_image_front, $nid_image_back_name, $nid_image_back];
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
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
            $tin_certificate_name = $tin_certificate_link = null;
            $tin_certificate = $this->financialRequest->getTinCertificate();

            if ($tin_certificate != 'null') {
                $tin_certificate_name = $this->isFile($tin_certificate) ? $tin_certificate->getClientOriginalName() : array_last(explode('/', $tin_certificate));
                $tin_certificate_link = $this->isFile($tin_certificate) ? $this->getPicture($this->profile, $tin_certificate, 'tin_certificate') : $tin_certificate;
            }
            $profile_data = [];
            if ($this->financialRequest->getTinNumber() == 'null') {
                $profile_data['tin_no'] = null;
            } else {
                $profile_data['tin_no'] = $this->financialRequest->getTinNumber();
            }
            if ($tin_certificate == 'null') {
                $profile_data['tin_certificate'] = null;
            } else {
                $profile_data['tin_certificate'] = $tin_certificate_link;
            }
            $this->profileRepository->update($this->profile, $profile_data);

            $profile_bank_data = [];
            if ($this->financialRequest->getBankName() == 'null') {
                $profile_bank_data['bank_name'] = null;
            } else {
                $profile_bank_data['bank_name'] = $this->financialRequest->getBankName();
            }
            if ($this->financialRequest->getBankAccNumber() == 'null') {
                $profile_bank_data['account_no'] = null;
            } else {
                $profile_bank_data['account_no'] = $this->financialRequest->getBankAccNumber();
            }
            if ($this->financialRequest->getBankAccNumber() == 'null') {
                $profile_bank_data['profile_id'] = null;
            } else {
                $profile_bank_data['profile_id'] = $this->profile->id;
            }
            if ($this->financialRequest->getBankAccNumber() != 'null') $this->profileBankInfoRepository->create($profile_bank_data);

            DB::commit();
            return [$this->profile, $tin_certificate_name, $tin_certificate_link];
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
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
            $member_data = [];
            if ($this->emergencyRequest->getEmergencyContractPersonName() == 'null') {
                $member_data['emergency_contract_person_name'] = null;
            } else {
                $member_data['emergency_contract_person_name'] = $this->emergencyRequest->getEmergencyContractPersonName();
            }

            if ($this->emergencyRequest->getEmergencyContractPersonMobile() == 'null') {
                $member_data['emergency_contract_person_number'] = null;
            } else {
                $member_data['emergency_contract_person_number'] = formatMobile($this->emergencyRequest->getEmergencyContractPersonMobile());
            }
            if ($this->emergencyRequest->getRelationshipEmergencyContractPerson() == 'null') {
                $member_data['emergency_contract_person_relationship'] = null;
            } else {
                $member_data['emergency_contract_person_relationship'] = $this->emergencyRequest->getRelationshipEmergencyContractPerson();
            }
            $this->memberRepository->update($this->member, $member_data);
            DB::commit();
            return $this->member;
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
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
            $business_member_data = ['status' => $this->coWorkerRequester->getStatus()];
            $this->businessMember = $this->businessMemberUpdater->setBusinessMember($this->businessMember)->update($business_member_data);
            DB::commit();
            return $this->businessMember;
        } catch (Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
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

    /**
     * @param $mobile
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        $profile = $this->profileRepository->checkExistingMobile($mobile);
        if (!$profile) return $this;
        if ($profile->id != $this->member->profile->id)
            $this->setError(400, 'This mobile number belongs to another member. Please contact with sheba');

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
        if ($profile->member->id == $this->member->id) return $this;
        if ($profile->member->businesses()->where('businesses.id', $this->business->id)->count() > 0) {
            $this->setError(409, "This person is already added");
        }
        if ($profile->member->businesses()->where('businesses.id', '<>', $this->business->id)->count() > 0) {
            $this->setError(422, "This person is already added with another business");
        }

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
}
