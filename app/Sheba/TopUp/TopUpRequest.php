<?php namespace Sheba\TopUp;

class TopUpRequest
{
    private $mobile;
    private $amount;
    private $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return TopUpRequest
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return (double)$this->amount;
    }

    /**
     * @param mixed $amount
     * @return TopUpRequest
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return TopUpRequest
     */
    public function setMobile($mobile)
    {
        $this->mobile = formatMobile($mobile);
        return $this;
    }
}