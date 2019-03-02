<?php namespace Sheba\MovieTicket;

use App\Models\MovieTicketVendor;

interface MovieAgent
{
    public function buyTicket($vendor_id, $mobile_number, $amount, $type);

    public function movieTicketTransaction(MovieTicketTransaction $transaction);

    public function refund($amount, $log);

    public function calculateMovieTicketCommission($amount, MovieTicketVendor $movieTicketVendor);

    /**
     * @return MovieTicketCommission
     */
    public function getMovieTicketCommission();
}