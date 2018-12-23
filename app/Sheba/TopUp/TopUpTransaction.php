<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;

class TopUpTransaction
{
    private $log;
    private $amount;
    private $topUpOrder;

    /**
     * @param TopUpOrder $top_up_order
     * @return TopUpTransaction
     */
    public function setTopUpOrder(TopUpOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return TopUpTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $log
     * @return TopUpTransaction
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getTopUpOrder()
    {
        return $this->topUpOrder;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }
}