<?php

namespace App\Sheba\PartnerOrder;


use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Profile;
use Illuminate\Http\Request;

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
        try {
            return Customer::where('profile_id', $this->resource->profile_id)->firstOrFail();
        } catch (\Throwable $exception) {
            return $this->createCustomerProfile();
        }


    }

    public function createCustomerProfile()
    {
        try {
            $profile = Profile::findOrFail($this->resource->profile_id);
            $data = ['profile_id' => $profile->id, 'remember_token' => str_random(255), 'created_by' => 0, 'created_by_name' => $profile->name];
            $customer = Customer::create($data);
            $this->createCustomerDeliveryAddressFromPartnerAddress($customer);
            return $customer;
        } catch (\Throwable $exception) {
            app('sentry')->captureException($exception);
            return false;
        }
    }

    private function createCustomerDeliveryAddressFromPartnerAddress(Customer $customer)
    {
        $delivery_address = new CustomerDeliveryAddress();
        $delivery_address->address = $this->partner->address;
        $delivery_address->name = 'Office';
        $delivery_address->customer_id = $customer->id;
        $delivery_address->geo_informations = $this->partner->geo_inforamtion;
        $delivery_address->save();
    }
}