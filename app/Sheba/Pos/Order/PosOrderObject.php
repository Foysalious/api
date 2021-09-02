<?php namespace App\Sheba\Pos\Order;


use Sheba\Pos\Order\PosOrderTypes;

class PosOrderObject
{
    protected $id;
    protected $sales_channel;
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
     * @param mixed $type
     * @return PosOrderObject
     */
    public function setType($type): PosOrderObject
    {
        $this->type = $type;
        return $this;
    }

    public function get(): PosOrderObject
    {
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSalesChannel()
    {
        return $this->sales_channel;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}