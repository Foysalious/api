<?php namespace Sheba\Business;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Member;

class MemberManager
{
    public function createCustomerFromMember(Member $member)
    {
        $customer = new Customer();
        $customer->profile_id = $member->profile->id;
        $customer->remember_token = str_random(255);
        $customer->save();
        return $customer;
    }

    public function createAddress(Member $member, Business $business)
    {
        $address = new CustomerDeliveryAddress();
        $address->address = $business->address;
        $address->name = $business->name;
        $geo = json_decode($business->geo_informations);
        $address->geo_informations = $business->geo_informations;
        $address->location_id = HyperLocal::insidePolygon($geo->lat, $geo->lng)->with('location')->first()->location->id;
        $address->customer_id = $member->profile->customer->id;
        $address->mobile = $member->profile->mobile;
        $address->save();
        return $address;
    }
}
