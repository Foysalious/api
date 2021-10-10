<?php namespace App\Sheba\Pos\Order;


use Sheba\Pos\Order\PosOrderTypes;

class PosOrderObject
{
    private $id;
    private $sales_channel;
    private $customer;
    private $due;
    protected $type = PosOrderTypes::OLD_SYSTEM;

    /**
     * @param mixed $id
     * @return PosOrderObject
     */
    public function setId($id): PosOrderObject
    {
        $this->id = $id;
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
        $this->due = $due;
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
     * @param mixed $customer
     * @return PosOrderObject
     */
    public function setCustomer($customer): PosOrderObject
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSalesChannel()
    {
        return $this->sales_channel;
    }

    public function __get($value)
    {
        return $this->{$value};
    }

}