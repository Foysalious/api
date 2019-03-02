<?php namespace Sheba\MovieTicket;

use App\Models\TopUpVendor;
use Sheba\TopUp\MovieTicketTransaction;

interface MovieAgent
{
    public function buyTicket($vendor_id, $mobile_number, $amount, $type);

    public function movieTicketTransaction(MovieTicketTransaction $transaction);

    public function refund($amount, $log);

    public function calculateCommission($amount, TopUpVendor $topup_vendor);

    /**
     * @return MovieTicketCommission
     */
    public function getCommission();
}