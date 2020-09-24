<?php namespace Sheba\Business\Vendor;


class UpdateRequest
{
    private $vendor;
    private $isActiveForB2B;

    /**
     * @return mixed
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param $vendor
     * @return UpdateRequest
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActiveForB2B()
    {
        return $this->isActiveForB2B;
    }

    /**
     * @param $is_active_for_b2b
     * @return UpdateRequest
     */
    public function setIsActiveForB2B($is_active_for_b2b)
    {
        $this->isActiveForB2B = $is_active_for_b2b;
        return $this;
    }
}