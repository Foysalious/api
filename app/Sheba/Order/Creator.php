<?php namespace Sheba\Order;

use App\Models\Customer;
use GuzzleHttp\Client;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

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

    public function create()
    {
        $created_by_type = $this->getModifierType();
        $client = new Client();
        $url = config('sheba.api_url') . "/v3/customers/".$this->customer->id."/orders";
        $res = $client->request('POST', $url, ['form_params' => [
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
            'partner' => $this->partnerId,
            'portal_name' => request()->hasHeader('Portal-Name') ? request()->header('Portal-Name') : (!is_null(request('portal_name')) ? request('portal_name') : config('sheba.portal')),
            'ip' => !is_null(request('ip')) ? request('ip') : request()->ip(),
            'user_agent' => !is_null(request('user_agent')) ? request('user_agent') : request()->header('User-Agent'),
            'created_by_type' => $created_by_type ? $created_by_type : 'automatic'
        ]]);
        return json_decode($res->getBody());
    }
}