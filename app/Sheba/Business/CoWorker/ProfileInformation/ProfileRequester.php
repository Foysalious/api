<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use App\Models\BusinessMember;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Image;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileRequester
{
    use HasErrorCodeAndMessage;

    /*** @var BusinessMember*/
    private $businessMember;
    private $name;
    private $email;
    private $department;
    private $designation;
    /*** @var ProfileRequester */
    private $businessRole;
    private $joiningDate;
    private $gender;
    /*** @var ProfileRepositoryInterface $profileRepository**/
    private $profileRepository;
    private $manager;
    private $employeeType;
    private $grade;
    private $employeeId;
    private $emergencyContactName;
    private $emergencyContactMobile;
    private $emergencyContactRelation;
    private $mobile;
    private $dateOfBirth;
    private $address;
    private $nationality;
    private $nidNo;
    private $passportNo;
    private $bloodGroup;
    private $socialLinks;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    private $nidFrontImage;
    private $nidBackImage;
    private $passportImage;

    public function __construct()
    {
        $this->profileRepository = app(ProfileRepositoryInterface::class);
        $this->businessMemberRepository = app(BusinessMemberRepositoryInterface::class);
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        $this->checkEmailUsedWithAnotherBusinessMember();
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setDepartment($department)
    {
        $this->department = $department;
        return $this;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function setDesignation($designation)
    {
        $this->designation = $designation;
        return $this;
    }

    public function getDesignation()
    {
        return $this->designation;
    }

    public function setJoiningDate($joining_date)
    {
        $this->joiningDate = $joining_date;
        return $this;
    }

    public function getJoiningDate()
    {
        return $this->joiningDate;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setManager($manager)
    {
        $this->manager = $manager;
        return $this;
    }

    public function getManager()
    {
        return $this->manager;
    }

    public function setEmployeeType($employee_type)
    {
        $this->employeeType = $employee_type;
        return $this;
    }

    public function getEmployeeType()
    {
        return $this->employeeType;
    }

    public function setEmployeeId($employee_id)
    {
        $this->employeeId = $employee_id;
        return $this;
    }

    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    public function setGrade($grade)
    {
        $this->grade = $grade;
        return $this;
    }

    public function getGrade()
    {
        return $this->grade;
    }

    public function setEmergencyContactName($emergency_contact_name)
    {
        $this->emergencyContactName = $emergency_contact_name;
        return $this;
    }

    public function getEmergencyContactName()
    {
        return $this->emergencyContactName;
    }

    public function setEmergencyContactMobile($emergency_contact_name)
    {
        $this->emergencyContactMobile = $emergency_contact_name ? formatMobile($emergency_contact_name) : null;
        return $this;
    }

    public function getEmergencyContactMobile()
    {
        return $this->emergencyContactMobile;
    }

    public function setEmergencyContactRelation($emergency_contact_mobile)
    {
        $this->emergencyContactRelation = $emergency_contact_mobile;
        return $this;
    }

    public function getEmergencyContactRelation()
    {
        return $this->emergencyContactRelation;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile ? formatMobile($mobile) : null;
        if ($this->mobile) $this->checkMobileUsedWithAnotherBusinessMember();
        return $this;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setDateOfBirth($date_of_birth)
    {
        $this->dateOfBirth = $date_of_birth;
        return $this;
    }

    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getNationality()
    {
        return $this->nationality;
    }

    public function setNidNo($nid_no)
    {
        $this->nidNo = $nid_no;
        return $this;
    }

    public function getNidNo()
    {
        return $this->nidNo;
    }

    public function setPassportNo($passport_no)
    {
        $this->passportNo = $passport_no;
        return $this;
    }

    public function getPassportNo()
    {
        return $this->passportNo;
    }

    public function setBloodGroup($blood_group)
    {
        $this->bloodGroup = $blood_group;
        return $this;
    }

    public function getBloodGroup()
    {
        return $this->bloodGroup;
    }

    public function setSocialLinks($social_links)
    {
        $this->socialLinks = $social_links;
        return $this;
    }

    public function getSocialLinks()
    {
        return $this->socialLinks;
    }

    public function setNidFrontImage($nid_front_image)
    {
        $this->nidFrontImage = $nid_front_image;
        return $this;
    }

    public function getNidFrontImage()
    {
        return $this->nidFrontImage;
    }

    public function setNidBackImage($nid_back_image)
    {
        $this->nidBackImage = $nid_back_image;
        return $this;
    }

    public function getNidBackImage()
    {
        return $this->nidBackImage;
    }

    public function setPassportImage($passport_image)
    {
        $this->passportImage = $passport_image;
        return $this;
    }

    public function getPassportImage()
    {
        return $this->passportImage;
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

    private function checkMobileUsedWithAnotherBusinessMember()
    {
        $business_member = $this->businessMemberRepository->checkExistingMobile($this->mobile);
        if (!$business_member) return $this;
        if ($business_member->id != $this->businessMember->id)
            $this->setError(400, 'This mobile number belongs to another member. Please contact with sheba');

        return $this;
    }

}
