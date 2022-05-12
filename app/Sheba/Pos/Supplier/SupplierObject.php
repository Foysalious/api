<?php


namespace Sheba\Pos\Supplier;


class SupplierObject
{
    private $id;
    private $partnerId;
    private $name;
    private $mobile;
    private $email;
    private $gender;
    private $dob;
    private $pro_pic;
    private $company_name;
    private $address;

    /**
     * @param mixed $id
     * @return SupplierObject
     */
    public function setId($id): SupplierObject
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $partnerId
     * @return SupplierObject
     */
    public function setPartnerId($partnerId): SupplierObject
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $name
     * @return SupplierObject
     */
    public function setName($name): SupplierObject
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $mobile
     * @return SupplierObject
     */
    public function setMobile($mobile): SupplierObject
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param mixed $email
     * @return SupplierObject
     */
    public function setEmail($email): SupplierObject
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param mixed $gender
     * @return SupplierObject
     */
    public function setGender($gender): SupplierObject
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @param mixed $dob
     * @return SupplierObject
     */
    public function setDob($dob): SupplierObject
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @param mixed $pro_pic
     * @return SupplierObject
     */
    public function setProPic($pro_pic): SupplierObject
    {
        $this->pro_pic = $pro_pic;
        return $this;
    }

    /**
     * @param mixed $company_name
     * @return SupplierObject
     */
    public function setCompanyName($company_name): SupplierObject
    {
        $this->company_name = $company_name;
        return $this;
    }

    /**
     * @param mixed $address
     * @return SupplierObject
     */
    public function setAddress($address): SupplierObject
    {
        $this->address = $address;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }
}