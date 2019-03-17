<?php namespace Sheba\MovieTicket;

use App\Models\MovieTicketOrder;

class MovieTicketTransaction
{
    private $log;
    private $amount;
    private $movieTicketOrder;

    /**
     * @param MovieTicketOrder $movie_ticket_order
     * @return MovieTicketTransaction
     */
    public function setMovieTicketOrder(MovieTicketOrder $movie_ticket_order)
    {
        $this->movieTicketOrder = $movie_ticket_order;
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