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
    private $nidFont;
    private $nidBack;

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
        dd($date_of_birth);
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
     * @param $nid_font
     * @return $this
     */
    public function setNidFont($nid_font)
    {
        $this->nidFont = $nid_font;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNidFont()
    {
        return $this->nidFont;
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
}