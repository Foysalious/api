<?php namespace Sheba\Pos\Payment;


class PosOrderPayment
{
    private $pos_order_id;
    private $amount;
    private $method;
    private $emi_month;
    private $interest;
    private $creator;
    private $isNewSystemPosOrder;

    /**
     * PosOrderPayment constructor.
     * @param $creator
     */
    public function __construct(Creator $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @param mixed $pos_order_id
     * @return PosOrderPayment
     */
    public function setPosOrderId($pos_order_id)
    {
        $this->pos_order_id = $pos_order_id;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return PosOrderPayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $method
     * @return PosOrderPayment
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return PosOrderPayment
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return PosOrderPayment
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $isNewSystemPosOrder
     * @return PosOrderPayment
     */
    public function setIsNewSystemPosOrder($isNewSystemPosOrder)
    {
        $this->isNewSystemPosOrder = $isNewSystemPosOrder;
        return $this;
    }

    private function makeData()
    {
        return [
            'pos_order_id' => $this->pos_order_id,
            'amount'       => $this->amount,
            'method'       => $this->method,
            'emi_month'    => $this->emi_month,
            'interest'     => $this->interest,
        ];
    }

    public function credit()
    {
        $this->creator->credit($this->makeData(), $this->isNewSystemPosOrder);
    }

}