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
        $this->phone = $this->isNull($phone) ? null : formatMobile($phone);
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
        $this->dateOfBirth = $this->isNull($date_of_birth) ? null : $date_of_birth;
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
        dd($address,$this->isNull($address));
        $this->address = $this->isNull($address) ? null : $address;
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
        $this->nationality = $this->isNull($nationality) ? null : $nationality;
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
        $this->nidNumber = $this->isNull($nid_number) ? null : $nid_number;
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
        $this->nidFront = $this->isNull($nid_front) ? null : $nid_front;
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
        $this->nidBack = $this->isNull($nid_back) ? null : $nid_back;
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