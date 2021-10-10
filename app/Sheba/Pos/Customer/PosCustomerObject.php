<?php namespace Sheba\Pos\Customer;


class PosCustomerObject
{
    private $id;
    private $partnerId;
    private $name;
    private $is_supplier;
    private $mobile;
    private $email;
    private $gender;
    private $dob;
    private $pro_pic;

    /**
     * @param mixed $id
     * @return PosCustomerObject
     */
    public function setId($id): PosCustomerObject
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param mixed $partnerId
     * @return PosCustomerObject
     */
    public function setPartnerId($partnerId): PosCustomerObject
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $name
     * @return PosCustomerObject
     */
    public function setName($name): PosCustomerObject
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $is_supplier
     * @return PosCustomerObject
     */
    public function setIsSupplier($is_supplier): PosCustomerObject
    {
        $this->is_supplier = $is_supplier;
        return $this;
    }

    /**
     * @param mixed $mobile
     * @return PosCustomerObject
     */
    public function setMobile($mobile): PosCustomerObject
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @param mixed $email
     * @return PosCustomerObject
     */
    public function setEmail($email): PosCustomerObject
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param mixed $gender
     * @return PosCustomerObject
     */
    public function setGender($gender): PosCustomerObject
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @param mixed $dob
     * @return PosCustomerObject
     */
    public function setDob($dob): PosCustomerObject
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @param mixed $pro_pic
     * @return PosCustomerObject
     */
    public function setProPic($pro_pic): PosCustomerObject
    {
        $this->pro_pic = $pro_pic;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }
}