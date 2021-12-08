<?php namespace App\Sheba\Customer\Events;

use App\Events\Event;
use App\Models\PartnerPosCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class AccountingCustomerCreate extends Event
{
    use SerializesModels, DispatchesJobs;

    /**
     * @var PartnerPosCustomer
     */
    private $accountingCustomer;
    private $id;
    private $name;
    private $mobile;

    public function getCustomerId()
    {
        return $this->id;
    }

    public function getCustomerName()
    {
        return $this->name;
    }

    public function getCustomerMobile()
    {
        return $this->mobile;
    }

    /**
     * AccountingCustomerCreate constructor.
     * @param PartnerPosCustomer $customer
     */
    public function __construct(PartnerPosCustomer $customer)
    {
        $this->id = $customer->id;
        $this->name = $customer->name;
        $this->mobile = $customer->mobile;
    }
}
