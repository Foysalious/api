<?php namespace App\Sheba\Customer\Events;

use App\Events\Event;
use App\Models\PartnerPosCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;

class PartnerPosCustomerUpdatedEvent extends Event
{
    use SerializesModels, DispatchesJobs;

    /**
     * @var PartnerPosCustomer
     */
    private $accountingCustomer;
    private $id;
    private $name;
    private $mobile;
    private $partner_id;
    private $pro_pic;

    public function getCustomerId()
    {
        return $this->id;
    }

    public function getCustomerName()
    {
        return $this->name;
    }

    public function getCustomerPartnerID()
    {
        return $this->partner_id;
    }

    public function getCustomerMobile()
    {
        return $this->mobile;
    }

    public function getCustomerProfilePicture()
    {
        return $this->pro_pic;
    }

    /**
     * AccountingCustomerCreate constructor.
     * @param PartnerPosCustomer $customer
     */
    public function __construct(PartnerPosCustomer $customer)
    {
        $this->id = $customer->customer_id;
        $this->partner_id = $customer->partner_id;
        $this->name = $customer->nick_name ?? $customer->customer->profile->name;
        $this->mobile = $customer->customer->profile->mobile;
        $this->pro_pic = $customer->customer->profile->pro_pic;
    }
}
