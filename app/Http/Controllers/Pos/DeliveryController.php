<?php namespace App\Http\Controllers\Pos;

use App\Exceptions\DoNotReportException;
use App\Exceptions\HttpException;
use App\Http\Controllers\Controller;
use App\Sheba\Partner\Delivery\DeliveryService;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryCancelRequestError;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryServiceServerError;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryServiceServerHttpError;
use App\Sheba\Partner\Delivery\Methods;
use App\Sheba\Partner\Delivery\OrderPlace;
use GuzzleHttp\Exception\GuzzleException;
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

    public function getInfoForRegistrationV2(Request $request, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $info = $delivery_service->setPartner($partner)->getRegistrationInfo();
        return http_response($request, $info, 200, ['info' => $info]);
    }

    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function register(Request $request, DeliveryService $delivery_service)
    {
        try {
            $registration = $this->registerCore($request, $delivery_service);
            return api_response($request, null, 200, ['messages' => 'আপনার রেজিস্ট্রেশন সফল হয়েছে', 'data' => $registration['data']]);
        } catch (GuzzleException $e) {
            list($http_code,$message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return api_response($request, null, $http_code, ['message' => $message]);
            return api_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws DeliveryServiceServerHttpError
     */
    public function registerV2(Request $request, DeliveryService $delivery_service)
    {
        try {
            $registration = $this->registerCore($request, $delivery_service);
            return http_response($request, null, 200, ['messages' => 'আপনার রেজিস্ট্রেশন সফল হয়েছে', 'data' => $registration['data']]);
        } catch (GuzzleException $e) {
            list($http_code,$message) = $this->resolveRegistrationError($e);
            if ($http_code > 399 && $http_code < 401) return http_response($request, null, $http_code, ['error_messages' => $message]);
            elseif ($http_code > 400 && $http_code < 500) return http_response($request, null, $http_code, ['message' => $message]);
            return http_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }

    private function registerCore(Request $request, DeliveryService $delivery_service)
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
        return $registration;
    }

    private function resolveError(GuzzleException $e)
    {
        $res = $e->getResponse();
        $http_code = $res->getStatusCode();
        $message = $res->getBody()->getContents();
        $decoded_message = json_decode($message, true);
        if (isset($decoded_message['errors']))
            $message = array_values($decoded_message['errors'])[0][0];
        else
            $message = $decoded_message['message'];
        return [$http_code,$message];
    }

    private function resolveRegistrationError(GuzzleException $e)
    {
        $res = $e->getResponse();
        $http_code = $res->getStatusCode();
        $message = $res->getBody()->getContents();
        $decoded_message = json_decode($message, true);
        if (isset($decoded_message['errors']))
        {
            $converted_message = [];
            foreach($decoded_message['errors'] as $key => $value)
                $converted_message[$key] = $value[0];
            return [400,$converted_message];
        }
        else
            $message = $decoded_message['message'];
        return [422,$message];
    }


    /**
     * @param Request $request
     * @param OrderPlace $orderPlace
     * @return JsonResponse
     * @throws DeliveryServiceServerError
     * @throws DeliveryServiceServerHttpError
     */
    public function orderPlace(Request $request, OrderPlace $orderPlace)
    {
        try{
            $orderPlaceInfo = $this->orderPlaceCore($request,$orderPlace);
            return api_response($request, null, 200, ['messages' => 'ডেলিভারি রিকোয়েস্ট সম্পন্ন', 'data' => $orderPlaceInfo['data']]);
        } catch (GuzzleException $e) {
            list($http_code,$message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return api_response($request, null, $http_code, ['message' => $message]);
            return api_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws DeliveryServiceServerError
     * @throws DeliveryServiceServerHttpError
     */
    public function orderPlaceV2(Request $request, OrderPlace $orderPlace)
    {
        try {
            $orderPlaceInfo = $this->orderPlaceCore($request, $orderPlace);
            return http_response($request, null, 200, ['messages' => 'ডেলিভারি রিকোয়েস্ট সম্পন্ন', 'data' => $orderPlaceInfo['data']]);
        } catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return http_response($request, null, $http_code, ['message' => $message]);
            return http_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }

    public function orderPlaceCore(Request $request, OrderPlace $orderPlace)
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

        $orderPlace->setPartner($partner)->setPosOrder($request->pos_order_id)->setPickupAddress($request->pickup_address)->setPickupThana($request->pickup_thana)->storeDeliveryInformation($orderPlaceInfo['data']);
        return $orderPlaceInfo;
    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     */
    public function vendorUpdate(Request $request, DeliveryService $delivery_service)
    {
        $this->vendorUpdateCore($request,$delivery_service);
        return api_response($request, null, 200);
    }

    public function vendorUpdateV2(Request $request, DeliveryService $delivery_service)
    {
        $this->vendorUpdateCore($request,$delivery_service);
        return http_response($request, null, 200);
    }

    private function vendorUpdateCore(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'vendor_name' => 'required|in:' . implode(',', Methods::get())
        ]);
        $partner = $request->auth_user->getPartner();
        $delivery_service->setPartner($partner)->setVendorName($request->vendor_name)->updateVendorInformation();
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

    public function getVendorListV2(Request $request, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $data = $delivery_service->setPartner($partner)->vendorlistWithSelectedDeliveryMethodV2();
        return http_response($request, null, 200, ['data' => $data]);
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
     * @throws HttpException
     */
    public function getOrderInformationV2(Request $request, $order_id, DeliveryService $delivery_service)
    {
        $partner = $request->auth_user->getPartner();
        $order_information = $delivery_service->setPartner($partner)->setPosOrder($order_id)->getOrderInfoV2();
        return http_response($request, null, 200, ['order_information' => $order_information]);
    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DeliveryServiceServerHttpError|DeliveryServiceServerError
     */
    public function getDeliveryCharge(Request $request, DeliveryService $delivery_service)
    {
        try{
            $charge = $this->getDeliveryChargeCore($request,$delivery_service);
            return api_response($request, null, 200, ['delivery_charge' => $charge]);
        } catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return api_response($request, null, $http_code, ['message' => $message]);
            return api_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }

    }

    /**
     * @throws DeliveryServiceServerHttpError
     */
    public function getDeliveryChargeV2(Request $request, DeliveryService $delivery_service)
    {
        try{
            $charge = $this->getDeliveryChargeCore($request,$delivery_service);
            return http_response($request, null, 200, ['delivery_charge' => $charge]);
        } catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return http_response($request, null, $http_code, ['message' => $message]);
            return http_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }
    public function getDeliveryChargeCore(Request $request, DeliveryService $delivery_service)
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
        return $delivery_service->setPartner($partner)->setWeight($request->weight)->setpickupThana($request->pickup_thana)->setpickupDistrict($request->pickup_district)->setCashOnDelivery($request->cod_amount)->setDeliveryDistrict($request->delivery_district)->setDeliveryThana($request->delivery_thana)->deliveryCharge();
    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DeliveryServiceServerHttpError
     * @throws DeliveryServiceServerError
     */
    public function getDistricts(Request $request, DeliveryService $delivery_service)
    {
        try{
            $districts = $delivery_service->districts();
            return api_response($request, null, 200, ['districts' => $districts]);
        }catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return api_response($request, null, $http_code, ['message' => $message]);
            return api_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }

    }

    /**
     */
    public function getDistrictsV2(Request $request, DeliveryService $delivery_service)
    {
        try{
            $districts = $delivery_service->districts();
            return http_response($request, null, 200, ['districts' => $districts]);
        }catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return http_response($request, null, $http_code, ['message' => $message]);
            return http_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }

    }


    /**
     * @param Request $request
     * @param $district_name
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DeliveryServiceServerHttpError
     */
    public function getUpzillas(Request $request, $district_name, DeliveryService $delivery_service)
    {
        try{
            $upzillas = $delivery_service->upzillas($district_name);
            return api_response($request, null, 200, ['upzillas' => $upzillas]);
        }catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return api_response($request, null, $http_code, ['message' => $message]);
            return api_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }

    /**
     * @throws DeliveryServiceServerHttpError
     */
    public function getUpzillasV2(Request $request, $district_name, DeliveryService $delivery_service)
    {
        try{
            $upzillas = $delivery_service->upzillas($district_name);
            return http_response($request, null, 200, ['upzillas' => $upzillas]);
        }catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return http_response($request, null, $http_code, ['message' => $message]);
            return http_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }

    }


    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DeliveryServiceServerError|DoNotReportException
     */
    public function getDeliveryStatus(Request $request, DeliveryService $delivery_service)
    {
        try{
            $partner = $request->auth_user->getPartner();
            $this->validate($request, [
                'pos_order_id' => 'required',
            ]);
            $data = $delivery_service->setPartner($partner)->setToken($this->bearerToken($request))->setPosOrder($request->pos_order_id)->getDeliveryStatus();
            return api_response($request, null, 200, $data);
        }catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return api_response($request, null, $http_code, ['message' => $message]);
            return api_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }

    }

    /**
     * @throws DeliveryServiceServerHttpError|HttpException
     */
    public function getDeliveryStatusV2(Request $request, DeliveryService $delivery_service)
    {
        try{
            $partner = $request->auth_user->getPartner();
            $this->validate($request, [
                'pos_order_id' => 'required',
            ]);
            $data = $delivery_service->setPartner($partner)->setToken($this->bearerToken($request))->setPosOrder($request->pos_order_id)->getDeliveryStatusV2();
            return http_response($request, null, 200, $data);
        }catch (GuzzleException $e) {
            list($http_code, $message) = $this->resolveError($e);
            if ($http_code > 399 && $http_code < 500) return http_response($request, null, $http_code, ['message' => $message]);
            return http_response($request, null, $http_code, ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @param DeliveryService $delivery_service
     * @return JsonResponse
     * @throws DeliveryCancelRequestError|DoNotReportException
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

    public function cancelOrderV2(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'pos_order_id' => 'required',
        ]);
        $partner = $request->auth_user->getPartner();
        $delivery_service->setPartner($partner)->setToken($this->bearerToken($request))->setPosOrder($request->pos_order_id)->cancelOrderv2();
        return http_response($request, null, 200, ['messages' => 'ডেলিভারি অর্ডারটি বাতিল করা হয়েছে']);
    }

    public function paperflyDeliveryCharge(Request $request, DeliveryService $delivery_service)
    {
        $data = $delivery_service->getPaperflyDeliveryCharge();
        return api_response($request, null, 200, ['data' => $data]);
    }

    public function paperflyDeliveryChargeV2(Request $request, DeliveryService $delivery_service)
    {
        $data = $delivery_service->getPaperflyDeliveryCharge();
        return http_response($request, null, 200, ['data' => $data]);
    }

    private function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if (Str::startsWith($header, 'Bearer ')) {
            return Str::substr($header, 7);
        }
        return false;
    }

    public function deliveryStatusUpdate(Request $request, DeliveryService $delivery_service)
    {
        if(config('app.env') == 'production') {
            if($request->ip() != config('pos_delivery.server_ip')) {
                return api_response($request, null, 400);
            }
        }
        $this->validate($request, [
            'order_ref_no' => 'required',
            'status' => "required|string" ,
            'merchant_code' => "required|string"
        ]);
        $delivery_service->setDeliveryReqId($request->order_ref_no)
            ->setDeliveryStatus($request->status)
            ->setMerchantCode($request->merchant_code)
            ->updateDeliveryStatus();
        return api_response($request, null, 200);
    }

    public function deliveryStatusUpdateV2(Request $request, DeliveryService $delivery_service)
    {
        if (config('app.env') == 'production') {
            if ($request->ip() != config('pos_delivery.server_ip')) {
                return http_response($request, null, 400);
            }
        }
        $this->deliveryStatusUpdateCore($request, $delivery_service);
        return http_response($request, null, 200);
    }

    private function deliveryStatusUpdateCore(Request $request, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'order_ref_no' => 'required',
            'status' => "required|string",
            'merchant_code' => "required|string"
        ]);
        $delivery_service->setDeliveryReqId($request->order_ref_no)
            ->setDeliveryStatus($request->status)
            ->setMerchantCode($request->merchant_code)
            ->updateDeliveryStatus();
    }


}
