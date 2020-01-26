<?php namespace App\Sheba\Bondhu;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Requests\OrderCreateFromBondhuRequest;
use App\Models\Affiliate;
use App\Models\Affiliation;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Location;
use App\Models\Order;
use App\Models\Profile;
use App\Models\Service;
use App\Models\User;
use App\Sheba\Checkout\Checkout;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\OrderPlace\OrderPlace;
use Sheba\Portals\Portals;

class BondhuAutoOrderV3
{
    use ModificationFields;

    private $service_category, $profile, $affiliation, $portal;
    public $order, $customer, $request, $affiliate;

    public function __construct(OrderCreateFromBondhuRequest $request)
    {
        $this->request = $request;
        if (!isset($this->request->affiliate->id)) {
            $this->request->affiliate = Affiliate::find($this->request->affiliate);
        }

        $this->affiliate = $this->request->affiliate;
        $this->portal = $this->request->header('portal-name');

        $modifier = $this->request->has('created_by') && $this->isAsOfflineBondhu() ? User::find($this->request->created_by) : $this->affiliate;
        $this->setModifier($modifier);
    }

    public function setServiceCategoryName()
    {
        $services = json_decode($this->request->services);
        if (isset($services[0]->id)) {
            $this->service_category = Service::find($services[0]->id)->category->name;
            return true;
        } else {
            $this->service_category = 'Unknown Service';
            return false;
        }
    }

    public function placeV3(OrderCreateFromBondhuRequest $request, OrderPlace $order_place)
    {
        $this->setCustomer();
        $this->setAffiliation();
        return $this->generateOrderV3($request, $order_place);
    }

    public function setCustomer()
    {
        $mobile = $this->request->mobile;
        $this->profile = Profile::where('mobile', $mobile)->first();
        if ($this->profile) {
            $this->customer = $this->profile->customer;
            if (!$this->customer) $this->customer = $this->createCustomer($this->profile);
        } else {
            $this->profile = $this->createProfile();
            $this->customer = $this->createCustomer($this->profile);
        }

        return $this;
    }

    public function setAffiliation()
    {
        $affiliation = new Affiliation([
            'affiliate_id'      => $this->affiliate->id,
            'customer_name'     => $this->profile->name,
            'customer_mobile'   => $this->profile->mobile,
            'service'           => $this->service_category,
            'status'            => 'converted'
        ]);
        $affiliation->save();
        $this->affiliation = $affiliation;
        return $this;
    }

    public function isAsOfflineBondhu()
    {
        return $this->portal == Portals::ADMIN;
    }

    public function generateOrderV3(OrderCreateFromBondhuRequest $request, OrderPlace $order_place)
    {
        $this->setAddress();
        $this->setExtras();

        $order = $order_place
            ->setCustomer($this->customer)
            ->setDeliveryName($request->name)
            ->setDeliveryAddressId($request->address_id)
            ->setPaymentMethod($request->payment_method)
            ->setDeliveryMobile($request->mobile)
            ->setSalesChannel($request->sales_channel)
            ->setPartnerId($request->partner_id)
            ->setSelectedPartnerId($request->partner)
            ->setAdditionalInformation($request->additional_information)
            ->setAffiliationId($this->request->affiliation_id)
            ->setInfoCallId($request->info_call_id)
            ->setBusinessId($request->business_id)
            ->setCrmId($request->crm_id)
            ->setVoucherId($request->voucher)
            ->setServices($request->services)
            ->setScheduleDate($request->date)
            ->setScheduleTime($request->time)
            ->setVendorId($request->vendor_id)
            ->create();

        $this->order = $order;
        return $order;
    }

    private function setAddress()
    {
        if (!$this->request->has('address_id')) {
            $location = Location::find((int)$this->request->location);
            $geo = json_decode($location->geo_informations);
            $customer_address = new CustomerDeliveryAddress();
            $customer_address->address = $this->request->address;
            $customer_address->location_id = $this->request->location;
            $customer_address->geo_informations = json_encode(['lat' => $geo->lat, 'lng' => $geo->lng]);
            $customer_address->name = $this->profile->name;
            $this->withCreateModificationField($customer_address);
            $customer_address->customer_id = $this->customer->id;
            $customer_address->save();
            $this->request->merge(['address_id' => $customer_address->id]);
        }
    }

    private function setExtras()
    {
        $extra = ['affiliation_id' => $this->affiliation->id];
        if (!$this->isAsOfflineBondhu()) {
            $extra['created_by'] = $this->affiliate->id;
            $extra['created_by_name'] = 'Affiliate - ' . $this->affiliate->profile->name;
        }
        $this->request->merge($extra);
    }

    private function createProfile()
    {
        $profile = new Profile();
        $profile->mobile = $this->request->mobile;
        $profile->name = $this->request->name;
        $profile->remember_token = str_random(255);
        $profile->save();
        return $profile;
    }

    private function createCustomer(Profile $profile)
    {
        $customer = new Customer();
        $customer->profile_id = $profile->id;
        $customer->remember_token = str_random(255);
        $customer->save();
        return $customer;
    }
}
