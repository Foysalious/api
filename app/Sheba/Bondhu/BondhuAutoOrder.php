<?php namespace App\Sheba\Bondhu;

use App\Http\Requests\BondhuOrderRequest;
use App\Models\Affiliate;
use App\Models\Affiliation;
use App\Models\Customer;
use App\Models\Profile;
use App\Models\Service;
use App\Sheba\Checkout\Checkout;

class BondhuAutoOrder
{
    private $service_category, $profile, $affiliation;
    public $order, $customer, $request, $affiliate;

    public function __construct(BondhuOrderRequest $request)
    {
        $this->request = $request;
        if (!isset($this->request->affiliate->id)) {
            $this->request->affiliate = Affiliate::find($this->request->affiliate);
        }
        $this->affiliate = $this->request->affiliate;
    }

    public function place()
    {
        $this->setCustomer();
        $this->setAffiliation();
        return $this->generateOrder();
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
            'affiliate_id' => $this->affiliate->id,
            'customer_name' => $this->profile->name,
            'customer_mobile' => $this->profile->mobile,
            'service' => $this->service_category,
            'status' => 'converted'
        ]);
        $affiliation->save();
        $this->affiliation = $affiliation;
        return $this;
    }

    public function generateOrder()
    {
        $this->setAddress();
        $this->setExtras();
        $order = new Checkout($this->customer);
        $order = $order->placeOrder($this->request);
        $this->order = $order;
        return $order;
    }

    private function setAddress()
    {
        if (!$this->request->has('address_id')) {
            $customer_address = $this->customer->delivery_addresses()->create([
                'address' => $this->request->address,
                'location' => $this->request->location,
                'name' => $this->profile->name,
                'mobile' => $this->profile->mobile,
                'created_by' => $this->affiliate->id,
                'created_by_name' => $this->affiliate->name
            ]);
            $this->request->merge(['address_id' => $customer_address->id]);
        }
    }

    private function setExtras()
    {
        $this->request->merge([
            'affiliation_id' => $this->affiliation->id,
            'created_by' => $this->affiliate->id,
            'created_by_name' => 'Affiliate - ' . $this->affiliate->profile->name
        ]);
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