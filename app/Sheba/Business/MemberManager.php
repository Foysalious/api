<?php namespace Sheba\Business;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Member;
use Sheba\Location\Geo;

class MemberManager
{
    /**
     * @param Member $member
     * @return Customer
     */
    public function createCustomerFromMember(Member $member)
    {
        $customer = new Customer();
        $customer->profile_id = $member->profile->id;
        $customer->remember_token = str_random(255);
        $customer->save();
        return $customer;
    }

    /**
     * @param Member $member
     * @param Business $business
     * @param $delivery_address
     * @param Geo $geo
     * @return CustomerDeliveryAddress
     */
    public function createAddress(Member $member, Business $business, $delivery_address, Geo $geo)
    {
        $hyper_location = HyperLocal::insidePolygon($geo->getLat(), $geo->getLng())->with('location')->first();
        $geo_informations = json_encode(['lat' => $geo->getLat(), 'lng' => $geo->getLng()]);
        if (!$hyper_location) {
            $business_geo_info = json_decode($business->geo_informations);
            $hyper_location = HyperLocal::insidePolygon($business_geo_info->lat, $business_geo_info->lng)->with('location')->first();
            $geo_informations = json_encode(['lat' => $business_geo_info->lat, 'lng' => $business_geo_info->lng]);
        }
        $location = $hyper_location->location;

        $address = new CustomerDeliveryAddress();
        $address->address = $delivery_address;
        $address->name = $business->name;
        $address->geo_informations = $geo_informations;
        $address->location_id = $location->id;
        $address->customer_id = $member->profile->customer->id;
        $address->mobile = $member->profile->mobile;
        $address->save();

        return $address;
    }
}
