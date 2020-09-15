<?php namespace Sheba\Business;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Member;
use Sheba\Location\Geo;

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

    public function createAddress(Member $member, $delivery_address, Geo $geo, Business $business)
    {
        $address = new CustomerDeliveryAddress();
        $address->address = $delivery_address;
        $address->name = $business->name;
        $address->geo_informations = json_encode(['lat' => $geo->getLat(), 'lng' => $geo->getLng()]);
        $address->location_id = HyperLocal::insidePolygon($geo->getLat(), $geo->getLng())->with('location')->first()->location->id;
        $address->customer_id = $member->profile->customer->id;
        $address->mobile = $member->profile->mobile;
        $address->save();
        return $address;
    }
}
