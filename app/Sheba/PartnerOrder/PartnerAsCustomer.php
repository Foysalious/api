<?php

namespace App\Sheba\PartnerOrder;


use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Partner;
use App\Models\Profile;
use App\Models\Resource;
use Illuminate\Http\Request;
use Sheba\PartnerOrder\Exceptions\PartnerAddressNotFound;
use Sheba\Voucher\Creator\Referral;

class PartnerAsCustomer
{
    /** @var Partner */
    public $partner;
    /** @var Resource $resource */
    public $resource;

    public function __construct(Request $request)
    {
        $this->partner = $request->partner;
        $this->resource = $request->manager_resource;
    }

    /**
     * @return Customer
     * @throws PartnerAddressNotFound
     * @throws HyperLocationNotFoundException
     */
    public function getCustomerProfile()
    {
        $customer = Customer::where('profile_id', $this->resource->profile_id)->first();
        if (!$customer) $customer = $this->createCustomerProfile();
        $hyper= $this->partner->getHyperLocation();
        if (!empty($hyper)&&!empty($hyper->location)){
            $address_count = $customer->delivery_addresses()->where('location_id', $hyper->location->id)->count();
            if ($address_count == 0) $this->createCustomerDeliveryAddressFromPartnerAddress($customer);
        }else{
            throw new HyperLocationNotFoundException();
        }
        return $customer;
    }

    /**
     * @return Customer
     * @throws PartnerAddressNotFound
     */
    public function createCustomerProfile()
    {
        $profile = Profile::findOrFail($this->resource->profile_id);
        $data = ['profile_id' => $profile->id, 'remember_token' => str_random(255)];
        $customer = Customer::create($data);
        new Referral($customer);
        $this->createCustomerDeliveryAddressFromPartnerAddress($customer);
        return $customer;
    }

    /**
     * @param Customer $customer
     * @throws PartnerAddressNotFound
     */
    private function createCustomerDeliveryAddressFromPartnerAddress(Customer $customer)
    {
        if (empty($this->partner->address)){
            throw new PartnerAddressNotFound();
        }
        $hyper = $this->partner->getHyperLocation();
        $geo = json_decode($this->partner->geo_informations);
        $delivery_address = new CustomerDeliveryAddress();
        $delivery_address->address = $this->partner->address;
        $delivery_address->name = 'Office';
        $delivery_address->customer_id = $customer->id;
        $delivery_address->geo_informations = json_encode(array('lat' => $geo->lat, 'lng' => $geo->lng));
        $delivery_address->location_id = isset($hyper) ? $hyper->location_id : null;
        $delivery_address->save();
    }
}
