<?php namespace Sheba\Business\CoWorker\Requests;

use App\Models\BusinessMember;

class PersonalRequest
{
    private $businessMember;
    private $phone;
    private $dateOfBirth;
    private $address;
    private $nationality;
    private $nidNumber;
    private $nidFront;
    private $nidBack;
    private $gender;
    private $passportNo;
    private $passportImage;
    private $bloodGroup;
    private $socialLinks;

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = BusinessMember::findOrFail($business_member);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    /**
     * @param $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param $date_of_birth
     * @return $this
     */
    public function setDateOfBirth($date_of_birth)
    {
        $this->dateOfBirth = $date_of_birth;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param $nationality
     * @return $this
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param $nid_number
     * @return $this
     */
    public function setNidNumber($nid_number)
    {
        $this->nidNumber = $nid_number;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNidNumber()
    {
        return $this->nidNumber;
    }

    /**
     * @param $nid_front
     * @return $this
     */
    public function setNidFront($nid_front)
    {
        $this->nidFront = $nid_front;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNidFront()
    {
        return $this->nidFront;
    }

    /**
     * @param $nid_back
     * @return $this
     */
    public function setNidBack($nid_back)
    {
        $this->nidBack = $nid_back;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNidBack()
    {
        return $this->nidBack;
    }

    /**
     * @param $passport_no
     * @return $this
     */
    public function setPassportNo($passport_no)
    {
        $this->passportNo = $passport_no;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassportNo()
    {
        return $this->passportNo;
    }

    /**
     * @param $passport_image
     * @return $this
     */
    public function setPassportImage($passport_image)
    {
        $this->passportImage = $passport_image;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassportImage()
    {
        return $this->passportImage;
    }

    /**
     * @param $gender
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = ucfirst($gender);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param $blood_group
     * @return $this
     */
    public function setBloodGroup($blood_group)
    {
        $this->bloodGroup = $blood_group;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBloodGroup()
    {
        return $this->bloodGroup;
    }

    public function setSocialLinks($social_links)
    {
        $this->socialLinks = $social_links;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSocialLinks()
    {
        return $this->socialLinks;
    }

    /**
     * @param $data
     * @return bool
     */
    private function isNull($data)
    {
        if ($data == 'null') return true;
        if ($data == null) return true;
        return false;
    }
}