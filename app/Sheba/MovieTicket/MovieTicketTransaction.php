<?php namespace Sheba\TopUp;

use App\Models\MovieTicketOrder;

class MovieTicketTransaction
{
    private $log;
    private $amount;
    private $movieTicketOrder;

    /**
     * @param MovieTicketOrder $top_up_order
     * @return MovieTicketTransaction
     */
    public function setTopUpOrder(MovieTicketOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        return $this;
    }

    /**
     * @param mixed $amount
     * @return MovieTicketTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $log
     * @return MovieTicketTransaction
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
    public function getMovieTicketOrder()
    {
        return $this->movieTicketOrder;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }
}