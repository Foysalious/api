<?php

namespace App\Sheba\PartnerOrder;


use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Profile;
use Illuminate\Http\Request;
use Sheba\Voucher\Creator\Referral;

class PartnerAsCustomer
{
    public $partner, $resource;

    public function __construct(Request $request)
    {
        $this->partner = $request->partner;
        $this->resource = $request->manager_resource;
    }

    public function getCustomerProfile()
    {
        $customer = Customer::where('profile_id', $this->resource->profile_id)->first();
        if (!$customer) $customer = $this->createCustomerProfile();
        return $customer;
    }

    public function createCustomerProfile()
    {
        $profile = Profile::findOrFail($this->resource->profile_id);
        $data = ['profile_id' => $profile->id, 'remember_token' => str_random(255)];
        $customer = Customer::create($data);
        new Referral($customer);
        $this->createCustomerDeliveryAddressFromPartnerAddress($customer);
        return $customer;
    }

    private function createCustomerDeliveryAddressFromPartnerAddress(Customer $customer)
    {
        $delivery_address = new CustomerDeliveryAddress();
        $delivery_address->address = $this->partner->address;
        $delivery_address->name = 'Office';
        $delivery_address->customer_id = $customer->id;
        $delivery_address->geo_informations = $this->partner->geo_inforamtion;
        $delivery_address->location_id = $this->partner->getHyperLocation()->location_id;
        $delivery_address->save();
    }
}