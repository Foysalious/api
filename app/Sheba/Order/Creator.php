<?php namespace Sheba\Order;

use App\Models\Customer;
use GuzzleHttp\Client;
use Sheba\RequestIdentification;

class Creator
{
    protected $customer;
    protected $services;
    protected $mobile;
    protected $date;
    protected $time;
    protected $addressId;
    protected $additionalInformation;
    protected $partnerId;
    protected $salesChannel;
    protected $paymentMethod;
    protected $deliveryName;
    protected $portalName;
    protected $resource;

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    public function setDeliveryName($deliveryName)
    {
        $this->deliveryName = trim($deliveryName);
        return $this;
    }

    public function setPortalName($portal)
    {
        $this->portalName = $portal;
        return $this;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    public function setAddressId($id)
    {
        $this->addressId = $id;
        return $this;
    }

    public function setAdditionalInformation($info)
    {
        $this->additionalInformation = $info;
        return $this;
    }

    public function setPartnerId($id)
    {
        $this->partnerId = $id;
        return $this;
    }

    public function setSalesChannel($salesChannel)
    {
        $this->salesChannel = $salesChannel;
        return $this;
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function create()
    {
        $client = new Client();
        $url = config('sheba.api_url') . "/v3/customers/" . $this->customer->id . "/orders";
        $form_params = [
            'services' => $this->services,
            'name' => $this->deliveryName,
            'mobile' => $this->mobile,
            'remember_token' => $this->customer->remember_token,
            'sales_channel' => $this->salesChannel,
            'payment_method' => $this->paymentMethod,
            'date' => $this->date,
            'time' => $this->time,
            'additional_information' => $this->additionalInformation,
            'address_id' => $this->addressId,
            'partner' => $this->partnerId
        ];
        if ($this->resource) {
            $form_params['created_by_type'] = 'App\Models\Resource';
            $form_params['created_by'] = $this->resource->id;
        }
        $res = $client->request('POST', $url, [
            'headers' => [
                'Portal-Name' => $this->portalName
            ],
            'form_params' => $form_params
        ]);
        return json_decode($res->getBody());
    }
}