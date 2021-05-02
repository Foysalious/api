<?php namespace App\Sheba\Partner\Delivery;


use App\Exceptions\DoNotReportException;
use App\Http\Requests\Request;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use Throwable;

class DeliveryService
{
    private $partner;
    private $cashOnDelivery;
    private $weight;
    private $pickupThana;
    private $pickupDistrict;
    private $deliveryThana;
    private $deliveryDistrict;


    public function __construct(DeliveryServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function setcashOnDelivery($cashOnDelivery)
    {
        $this->cashOnDelivery = $cashOnDelivery;
        return $this;
    }

    public function setpickupThana($pickupThana)
    {
        $this->pickupThana = $pickupThana;
        return $this;
    }

    public function setpickupDistrict($pickupDistrict)
    {
        $this->pickupDistrict = $pickupDistrict;
        return $this;
    }

    public function setDeliveryThana($deliveryThana)
    {
        $this->deliveryThana = $deliveryThana;
        return $this;
    }

    public function setDeliveryDistrict($deliveryDistrict)
    {
        $this->deliveryDistrict = $deliveryDistrict;
        return $this;
    }

    /**
     * @return mixed
     */
    public function vendorList()
    {


        $vendor_list = [];
        $all_vendor_list = config('pos_delivery.vendor_list');
        foreach ($all_vendor_list as $key => $list) {
            array_push($vendor_list, $list);
        }
        return $vendor_list;

    }

    public function getRegistrationInfo()
    {
        return [
            'mobile_banking_providers' => config('pos_delivery.mobile_banking_providers'),
            'merchant_name' => $this->partner->name,
            'contact_name' => $this->partner->getContactPerson(),
            'contact_number' => $this->partner->getContactNumber(),
            'email' => $this->partner->getContactEmail(),
            'business_type' => $this->partner->business_type,
            'address' => [
                'full_address' => $this->partner->deliveryInformation->address,
                'thana' => $this->partner->deliveryInformation->thana,
                'zilla' => $this->partner->deliveryInformation->district
            ],
        ];
    }

    public function getOrderInfo($order_id)
    {
        $order = PosOrder::where('id', $order_id)->with('customer', 'customer.profile', 'payments')->first();
        //       $order = PosOrder::where('id', $order_id)->first();
        if ($this->partner->id != $order->partner_id) {
            throw new DoNotReportException("Order does not belongs to this partner", 400);
        }
        return [
            'partner_pickup_information' => [
                'merchant_name' => $this->partner->name,
                'contact_person' => $this->partner->getContactPerson(),
                'mobile' => $this->partner->getContactNumber(),
                'email' => $this->partner->getContactEmail(),
                'business_type' => $this->partner->business_type,
                'address' => [
                    'full_address' => $this->partner->deliveryInformation->address,
                    'thana' => $this->partner->deliveryInformation->thana,
                    'zilla' => $this->partner->deliveryInformation->district
                ],
            ],
            'customer-delivery_information' => [
                'name' => $order->customer->profile->name,
                'number' => $order->customer->profile->mobile,
                'address' => [
                    'full_address' => $order->address,
                    'thana' => $order->delivery_thana,
                    'zilla' => $order->delivery_zilla
                ],
                'payment_method' => $this->paymentInfo($order_id)->method,
                'cash_amount' => $order->payments,

            ]
        ];
    }

    public function paymentInfo($order_id)
    {


        return PosOrderPayment::where('pos_order_id', $order_id)->where('transaction_type', 'Credit')->first();

    }

    public function makeData()
    {
        return [
            'name' => $this->name,
            ''

        ];
    }

    public function makeDataDeliveryCharge()
    {
        $data= [

            'weight' => $this->weight,
            'cod_amount' => $this->cashOnDelivery,
            'pick_up' => [
                'thana' => $this->pickupThana ,
                'district' => $this->pickupDistrict,
            ],
            'delivery' => [
                'thana' => $this->deliveryThana,
                'district' => $this->deliveryDistrict,
            ]
        ];
        return json_encode($data);
//        return '{
//        "weight": "2.5",
//    "cod_amount": 5000,
//    "pick_up":{
//            "thana": "Mohammadpur",
//        "district":"Manikganj"
//    },
//    "delivery":{
//            "thana": "Khilgaon",
//        "district":"Dhaka"
//    }
//}';
    }



    public function register()
    {
        try {
            $data = $this->makeData();
            return $this->client->post('', $data);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }

    }
    public function deliveryCharge()
    {

        $data = $this->makeDataDeliveryCharge();

        $client = new \GuzzleHttp\Client();
dd($data);
        return $client->post('https://dev-sdp-api.padmatechnology.com/api/v1/s-delivery/price-check', $data);
//        return $this->client->post('', $data);

    }

}
