<?php namespace Sheba\Checkout\SubscriptionOrderPlace;

use App\Models\HyperLocal;
use App\Models\Member;
use App\Sheba\Address\AddressValidator;
use Illuminate\Http\Request;
use Sheba\Business\MemberManager;
use Sheba\Checkout\Requests\SubscriptionOrderPartnerListRequest;
use Sheba\Location\Coords;
use Sheba\Map\Address;
use Sheba\Map\GeoCode;

class B2bSubscriptionOrderPlaceFactory extends SubscriptionOrderPlaceAbstractFactory
{
    /** @var AddressValidator */
    private $addressValidator;
    /** @var MemberManager */
    private $memberManager;

    public function __construct(SubscriptionOrderPartnerListRequest $subscription_order_request,
                                AddressValidator $address_validator, MemberManager $member_manager)
    {
        parent::__construct($subscription_order_request);
        $this->addressValidator = $address_validator;
        $this->memberManager = $member_manager;
    }

    protected function getCreator(Request $request)
    {
        return new SubscriptionOrderPlaceWithPartner();
    }

    /**
     * @param Request $request
     */
    protected function buildRequest(Request $request)
    {
        $business = $request->business;
        $member = $request->manager_member;
        $customer = $member->profile->customer;

        $address = app(Address::class); $geo_code = app(GeoCode::class);
        $business_geo_info = json_decode($business->geo_informations);
        $delivery_address = HyperLocal::insidePolygon($business_geo_info->lat, $business_geo_info->lng)->with('location')->first()->location->name;
        $address->setAddress($delivery_address);
        $geo = $geo_code->setAddress($address)->getGeo();

        if (!$customer) {
            $customer = $this->memberManager->createCustomerFromMember($member);
            $member = Member::find($member->id);
            $address = $this->memberManager->createAddress($member, $business, $delivery_address, $geo);
        } else {
            $coords = new Coords($geo->getLat(), $geo->getLng());
            $address = $this->addressValidator->isAddressLocationExists($customer->delivery_addresses, $coords);
            if (!$address) $address = $this->memberManager->createAddress($member, $business, $delivery_address, $geo);
            if (!$address->mobile) $address->update(['mobile' => formatMobile($member->profile->mobile)]);
        }

        $this->subscriptionOrderRequest->setRequest($request)->setSalesChannel(constants('SALES_CHANNELS')['B2B']['name'])
            ->setCustomer($customer)->setAddress($address)->setUser($business)
            ->setDeliveryMobile($address->mobile)->setDeliveryName($address->name)
            ->prepareObject();
    }
}
