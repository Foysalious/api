<?php namespace App\Sheba\Partner\Delivery;


use App\Models\Partner;
use App\Models\PosOrder;

class DeliveryService
{
    private $partner;


    public function __construct()
    {

    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function getRegistrationInfo()
    {
        return [
            'mobile_banking_providers' => config('pos_delivery.mobile_banking_providers'),
            'merchant_name' => $this->partner->name,
            'contact_person' => $this->partner->getContactPerson(),
            'mobile' => $this->partner->getContactNumber(),
            'email' => $this->partner->getContactEmail(),
            'business_type' => $this->partner->business_type,
            'address' => $this->partner->address,
        ];
    }

    public function getOrderInfo($order_id)
    {
        $order= PosOrder::where('id',$order_id)->partner_id->get();
        if ($this->partner->id != $order){
            return response(' Order Does not exist', 400);
        }
        else{
            return [
                'merchant_name' => $this->partner->name,
                'contact_person' => $this->partner->getContactPerson(),
                'mobile' => $this->partner->getContactNumber(),
                'email' => $this->partner->getContactEmail(),
                'business_type' => $this->partner->business_type,
                'address' => $this->partner->address,
            ];
        }

    }

}
