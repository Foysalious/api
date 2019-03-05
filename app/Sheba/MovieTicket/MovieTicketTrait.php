<?php namespace Sheba\MovieTicket;

use App\Models\MovieTicketVendor;
use App\Models\TopUpVendor;
use Sheba\MovieTicket\Vendor\VendorFactory;

trait MovieTicketTrait
{
    /**
     * @param $vendor_id
     * @param $name
     * @param $email
     * @param $mobile_number
     * @param $amount
     */
    public function buyTicket($name, $email, $mobile_number, $amount, $vendor_id = 1)
    {
        $vendor = (new VendorFactory())->getById($vendor_id);/** @var $this MovieAgent */

        $request = (new MovieTicketRequest())->setName($name)->setEmail($email)->setMobile($mobile_number)->setAmount($amount);
        (new MovieTicket())->setAgent($this)->setVendor($vendor)->buyTicket($request);
    }

    public function calculateMovieTicketCommission($amount, MovieTicketVendor $movieTicketVendor)
    {
        return (double)$amount * ($this->agentCommission($movieTicketVendor) / 100);
    }

    public function calculateAmbassadorCommissionForMovieTicket($amount, MovieTicketVendor $movieTicketVendor)
    {
        return (double)$amount * ($this->ambassadorCommission($movieTicketVendor) / 100);
    }

    public function agentCommissionForMovieTicket($movieTicketVendor)
    {
        return (double)$movieTicketVendor->commissions()->where('type', get_class($this))->first()->agent_commission;
    }

    public function ambassadorCommissionForMovieTicket($movieTicketVendor)
    {
        return (double)$movieTicketVendor->commissions()->where('type', get_class($this))->first()->ambassador_commission;
    }
}