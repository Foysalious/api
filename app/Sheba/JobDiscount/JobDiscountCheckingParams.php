<?php namespace Sheba\JobDiscount;

class JobDiscountCheckingParams
{
    /** @var float */
    private $orderAmount;
    /** @var float */
    private $discountableAmount;

    /**
     * @param $amount
     * @return $this
     */
    public function setOrderAmount($amount)
    {
        $this->orderAmount = $amount;
        return $this;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setDiscountableAmount($amount)
    {
        $this->discountableAmount = $amount;
        return $this;
    }

    /**
     * @return float
     */
    public function getOrderAmount()
    {
        return $this->orderAmount;
    }

    /**
     * @return float
     */
    public function getDiscountableAmount()
    {
        return $this->discountableAmount;
    }
}
