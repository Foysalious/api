<?php namespace App\Http\Controllers\Pos;

use App\Exceptions\DoNotReportException;
use App\Http\Controllers\Controller;
use App\Sheba\Partner\Delivery\DeliveryService;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryCancelRequestError;
use App\Sheba\Partner\Delivery\Methods;
use App\Sheba\Partner\Delivery\OrderPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Sheba\ModificationFields;
use Throwable;


class DeliveryController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function getInfoForRegistration(Request $request, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $info = $delivery_service->setPartner($partner)->getRegistrationInfo();
        return api_response($request, null, 200, ['info' => $info]);
    }

    /**
     * @param Request $request
     * @param $partner
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function register(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'district' => 'required',
            'thana' => 'required',
            'payment_method' => 'required|in:' . implode(',', config('pos_delivery.payment_method')),
            'contact_name' => 'required',
            'mobile' => 'required|mobile:bd',
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
        $partner = $request->auth_user->getPartner();
        $registration = $delivery_service
            ->setToken($this->bearerToken($request))
            ->setPartner($partner)
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
            ->setEmail($request->email)
            ->setAccountType($request->account_type)
            ->register();
        $delivery_service->setPartner($partner)->setAccountType($request->account_type)->storeDeliveryInformation($registration['data']);
        return api_response($request, null, 200, ['messages' => 'আপনার রেজিস্ট্রেশন সফল হয়েছে', 'data' => $registration['data']]);
    }


    /**
     * @param Request $request
     * @param $partner
     * @param OrderPlace $orderPlace
     * @return JsonResponse
     */
    public function orderPlace(Request $request, OrderPlace $orderPlace)
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
        $partner = $request->auth_user->getPartner();
        $orderPlaceInfo = $orderPlace
            ->setPartner($partner)
            ->setToken($this->bearerToken($request))
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


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function vendorUpdate(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'vendor_name' => 'required|in:' . implode(',', Methods::get())
        ]);
        $partner = $request->auth_user->getPartner();
        $delivery_service->setPartner($partner)->setVendorName($request->vendor_name)->updateVendorInformation();
        return api_response($request, null, 200);

    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function getVendorList(Request $request, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $data = $delivery_service->setPartner($partner)->vendorlistWithSelectedDeliveryMethod();
        return api_response($request, null, 200, ['data' => $data]);
    }


    /**
     * @param Request $request
     * @param $order_id
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DoNotReportException
     */
    public function getOrderInformation(Request $request, $order_id, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $order_information = $delivery_service->setPartner($partner)->setPosOrder($order_id)->getOrderInfo();
        return api_response($request, null, 200, ['order_information' => $order_information]);
    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function getDeliveryCharge(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'partner_id' => 'required',
            'weight' => 'required',
            'cod_amount' => 'required',
            'delivery_district' => 'required',
            'delivery_thana' => 'required',
            'pickup_thana' => 'sometimes',
            'pickup_district' => 'sometimes'
        ]);

        $partner = $request->partner_id;
        $charge = $delivery_service->setPartner($partner)->setWeight($request->weight)->setpickupThana($request->pickup_thana)->setpickupDistrict($request->pickup_district)->setCashOnDelivery($request->cod_amount)->setDeliveryDistrict($request->delivery_district)->setDeliveryThana($request->delivery_thana)->deliveryCharge();
        return api_response($request, null, 200, ['delivery_charge' => $charge]);
    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function getDistricts(Request $request, DeliveryService $delivery_service)
    {
        $districts = $delivery_service->districts();
        return api_response($request, null, 200, ['districts' => $districts]);
    }


    /**
     * @param Request $request
     * @param $district_name
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function getUpzillas(Request $request, $district_name, DeliveryService $delivery_service)
    {
        $upzillas = $delivery_service->upzillas($district_name);
        return api_response($request, null, 200, ['upzillas' => $upzillas]);
    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function getDeliveryStatus(Request $request, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $this->validate($request, [
            'pos_order_id' => 'required',
        ]);
        $statusInfo = $delivery_service->setPartner($partner)->setToken($this->bearerToken($request))->setPosOrder($request->pos_order_id)->getDeliveryStatus();
        return api_response($request, null, 200, ['status' => $statusInfo['data']['status']]);
    }

    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DeliveryCancelRequestError
     */
    public function cancelOrder(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'pos_order_id' => 'required',
        ]);
        $partner = $request->auth_user->getPartner();
        $delivery_service->setPartner($partner)->setToken($this->bearerToken($request))->setPosOrder($request->pos_order_id)->cancelOrder();
        return api_response($request, null, 200, ['messages' => 'ডেলিভারি অর্ডারটি বাতিল করা হয়েছে']);
    }

    public function paperflyDeliveryCharge(Request $request, DeliveryService $delivery_service)
    {
        $data = $delivery_service->getPaperflyDeliveryCharge();
        return api_response($request, null, 200, ['data' => $data]);

    }

    private function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
        return false;
    }

}
