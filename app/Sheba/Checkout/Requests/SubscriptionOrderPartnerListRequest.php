<?php namespace Sheba\Checkout\Requests;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SubscriptionOrderPartnerListRequest extends PartnerListRequest
{
    protected $salesChannel;
    protected $geo;
    /** @var $address CustomerDeliveryAddress */
    protected $address;
    /** @var $customer Customer */
    protected $customer;
    /** @var $billingCycleStart Carbon */
    protected $billingCycleStart;
    /** @var $user Model */
    protected $user;
    /** @var $billingCycleEnd Carbon */
    protected $billingCycleEnd;
    protected $location;
    protected $deliveryName;
    protected $deliveryMobile;
    protected $additionalInfo;

    public function prepareObject()
    {
        $this->setAdditionalInfo();
        $this->setGeo($this->geo->lat, $this->geo->lng);
        parent::prepareObject();
        $this->calculateBillingCycle();
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setAddress(CustomerDeliveryAddress $address)
    {
        $this->address = $address;
        $this->decodeGeo();
        $this->calculateLocation();
        return $this;
    }

    private function calculateLocation()
    {
        if ($this->address->location_id) $this->location = $this->address->location_id;
        else {
            $hyper_local = HyperLocal::insidePloygon($this->geo->lat, $this->geo->lng)->first();
            $this->location = $hyper_local->location_id;
        }
    }

    private function decodeGeo()
    {
        $this->geo = json_decode($this->address->geo_informations);
    }

    public function setSalesChannel($sales_channel)
    {
        $this->salesChannel = $sales_channel;
        return $this;
    }

    public function setDeliveryName($delivery_name)
    {
        $this->deliveryName = $delivery_name;
        return $this;
    }

    public function setDeliveryMobile($mobile)
    {
        $this->deliveryMobile = $mobile;
        return $this;
    }

    public function setUser(Model $user)
    {
        $this->user = $user;
        return $this;
    }

    private function setAdditionalInfo()
    {
        $this->additionalInfo = $this->request->additional_info;
    }

    private function calculateBillingCycle()
    {
        $this->billingCycleStart = Carbon::now();
        $this->billingCycleEnd = $this->getBillingCycleEnd();
    }

    private function getBillingCycleEnd()
    {
        $days = $this->isWeeklySubscription() ? 7 : 30;
        return $this->billingCycleStart->copy()->addDays($days);
    }
}
