<?php namespace App\Sheba\Pos\Order;


use Sheba\Pos\Order\PosOrderTypes;

class PosOrderObject
{
    private $id;
    private $customer_id;
    private $partner_id;
    private $sales_channel;
    private $customer;
    private $partner;
    private $is_migrated;
    private $created_at;
    private $due;
    protected $type = PosOrderTypes::OLD_SYSTEM;

    /**
     * @param mixed $id
     * @return PosOrderObject
     */
    public function setId($id): PosOrderObject
    {
        $this->id = (int)$id;
        return $this;
    }

    /**
     * @param mixed $customer_id
     * @return PosOrderObject
     */
    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param mixed $partner_id
     * @return PosOrderObject
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
        return $this;
    }

    /**
     * @param mixed $sales_channel
     * @return PosOrderObject
     */
    public function setSalesChannel($sales_channel): PosOrderObject
    {
        $this->sales_channel = $sales_channel;
        return $this;
    }

    /**
     * @param mixed $due
     * @return PosOrderObject
     */
    public function setDue($due)
    {
        $this->due = (float)$due;
        return $this;
    }


    /**
     * @param mixed $type
     * @return PosOrderObject
     */
    public function setType($type): PosOrderObject
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $is_migrated
     * @return PosOrderObject
     */
    public function setIsMigrated($is_migrated): PosOrderObject
    {
        $this->is_migrated = $is_migrated;
        return $this;
    }

    /**
     * @param mixed $customer
     * @return PosOrderObject
     */
    public function setCustomer($customer): PosOrderObject
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param mixed $partner
     * @return PosOrderObject
     */
    public function setPartner($partner): PosOrderObject
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $created_at
     * @return PosOrderObject
     */
    public function setCreatedAt($created_at): PosOrderObject
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

}