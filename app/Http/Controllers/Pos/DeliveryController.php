<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Sheba\Partner\Delivery\DeliveryService;
use App\Sheba\Partner\Delivery\OrderPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use Throwable;


class DeliveryController extends Controller
{
    use ModificationFields;

    public function getInfoForRegistration(Request $request, $partner, DeliveryService $delivery_service)
    {
        $partner = $request->partner;
        $this->setModifier($request->manager_resource);
        $info = $delivery_service->setPartner($partner)->getRegistrationInfo();
        return api_response($request, null, 200, ['info' => $info]);
    }

    /**
     * @param Request $request
     * @param $partner
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function register(Request $request, $partner, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'district' => 'required',
            'thana' => 'required',
            'payment_method' => 'required|in:' . implode(',', config('pos_delivery.payment_method')),
            'contact_name' => 'required',
            'mobile' => 'required',
            'account_type' => 'required|in:' . implode(',', config('pos_delivery.account_type')),
            'business_type' => 'required',
            'account_name' => 'sometimes',
            'account_number' => 'sometimes',
            'bank_name' => 'sometimes',
            'branch_name' => 'sometimes',
            'routing_number' => 'sometimes',
            'fb_page_url' => 'sometimes',
            'website' => 'sometimes',
            'email' => 'sometimes',
        ]);

        $partner = $request->partner;
        $this->setModifier($request->manager_resource);
        $registration = $delivery_service->setPartner($partner)
            ->setName($request->name)
            ->setAddress($request->address)
            ->setDistrict($request->district)
            ->setThana($request->thana)
            ->setPaymentMethod($request->payment_method)
            ->setContactName($request->contact_name)
            ->setPhone($request->mobile)
            ->setAccountName($request->account_name)
            ->setAccountNumber($request->account_number)
            ->setBankName($request->bank_name)
            ->setBranchName($request->branch_name)
            ->setRoutingNumber($request->routing_number)
            ->setProductNature($request->business_type)
            ->register();
        $delivery_service->setPartner($partner)->storeDeliveryInformation($registration['data']);
        return api_response($request, null, 200, ['messages' => 'আপনার রেজিস্ট্রেশন সফল হয়েছে', 'data' => $registration['data']]);
    }

//    public function vendorUpdate(Request $request, $partner, DeliveryService $delivery_service)
//    {
//        $partner = $request->partner;
//        $this->setModifier($request->manager_resource);
//        $registration = $delivery_service->setPartner($partner)
//            ->setVendor($request->vendor)
//            ->register();
//        $delivery_service->setPartner($partner)->storeDeliveryInformation($registration['data']);
//        return api_response($request, null, 200, ['messages' => 'আপনার রেজিস্ট্রেশন সফল হয়েছে', 'data' => $registration['data']]);
//    }

    public function orderPlace(Request $request, $partner, OrderPlace $orderPlace)
    {
        $this->validate($request, [
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'delivery_address' => 'required',
            'delivery_district' => 'required',
            'delivery_thana' => 'required',
            'weight' => 'required',
            'cod_amount' => 'required',
            'partner_name' => 'required',
            'partner_phone' => 'required',
            'pickup_address' => 'required',
            'pickup_district' => 'required',
            'pickup_thana' => 'required',
            'payment_method' => 'sometimes',
            'pos_order_id' => 'required'
        ]);
        $orderPlaceInfo = $orderPlace
            ->setPartner($partner)
            ->setCustomerName($request->customer_name)
            ->setCustomerPhone($request->customer_phone)
            ->setDeliveryAddress($request->delivery_address)
            ->setDeliveryDistrict($request->delivery_district)
            ->setDeliveryThana($request->delivery_thana)
            ->setWeight($request->weight)
            ->setCodAmount($request->cod_amount)
            ->setPartnerName($request->partner_name)
            ->setPartnerPhone($request->partner_phone)
            ->setPickupAddress($request->pickup_address)
            ->setPickupDistrict($request->pickup_district)
            ->setPickupThana($request->pickup_thana)
            ->orderPlace();

        $orderPlace->setPartner($partner)->setPosOrder($request->pos_order_id)->storeDeliveryInformation($orderPlaceInfo['data']);
        return api_response($request, null, 200, ['messages' => 'ডেলিভারি রিকোয়েস্ট সম্পন্ন', 'data' => $orderPlaceInfo['data']]);
    }


    public function getVendorList(Request $request, DeliveryService $delivery_service)
    {
        $vendor = $delivery_service->vendorlist();
        return api_response($request, null, 200, ['delivery_vendor' => $vendor]);
    }

    public function getOrderInformation(Request $request, $partner, $order_id, DeliveryService $delivery_service)
    {

        $partner = $request->partner;

        $this->setModifier($request->manager_resource);

        $order_information = $delivery_service->setPartner($partner)->getOrderInfo($order_id);
        return api_response($request, null, 200, ['order_information' => $order_information]);

    }

    public function deliveryCharge(Request $request, $partner, DeliveryService $delivery_service)
    {



        $partner = $request->partner;

//        $this->setModifier($request->manager_resource);


        $charge = $delivery_service->setPartner($partner)->setWeight($request->weight)->setcashOnDelivery($request->cod_amount)->setDeliveryDistrict($request->deliveryDistrict)->setDeliveryThana($request->deliveryThana)->deliveryCharge($partner);

        return api_response($request, null, 200, ['info' => $charge]);

    }

    public function districts(Request $request, $partner, DeliveryService $delivery_service)
    {
//        $partner= $request->partner;
//        $this->setModifier($request->manager_resource);
        $district = $delivery_service->districts();
        return api_response($request, null, 200, ['district' => $district]);
    }

    public function upzillas(Request $request, $partner, $district_name, DeliveryService $delivery_service)
    {
//        $partner= $request->partner;
//        $this->setModifier($request->manager_resource);
        $upzillas = $delivery_service->upzillas($district_name);
        return api_response($request, null, 200, ['upzillas' => $upzillas]);
    }


    public function getDeliveryStatus(Request $request, $partner, DeliveryService $delivery_service)
    {
        $this->validate($request,[
           'pos_order_id' => 'required',
        ]);
        $statusInfo = $delivery_service->setPartner($partner)->setPosOrder($request->pos_order_id)->getDeliveryStatus();
        return api_response($request, null, 200, ['status' => $statusInfo['data']['status']]);
    }

    public function cancelOrder(Request $request, $partner, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'pos_order_id' => 'required',
        ]);
        $delivery_service->setPartner($partner)->setPosOrder($request->pos_order_id)->cancelOrder();
        return api_response($request, null, 200, ['messages' => 'ডেলিভারি অর্ডারটি বাতিল করা হয়েছে']);
    }



}
