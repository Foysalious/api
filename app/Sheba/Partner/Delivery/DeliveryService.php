<?php namespace App\Sheba\Partner\Delivery;


use App\Exceptions\DoNotReportException;
use App\Http\Requests\Request;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;

class DeliveryService
{
    private $partner;


    public function __construct()
    {

    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
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
            'contact_person' => $this->partner->getContactPerson(),
            'mobile' => $this->partner->getContactNumber(),
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


}
