<?php namespace App\Sheba\Business\CoWorker\ProfileInformation;


use App\Models\BusinessMember;
use Sheba\Helpers\HasErrorCodeAndMessage;
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

    public function __construct()
    {
        $this->profileRepository = app(ProfileRepositoryInterface::class);
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

}
