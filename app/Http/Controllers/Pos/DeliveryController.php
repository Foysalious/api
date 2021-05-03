<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Sheba\Partner\Delivery\DeliveryService;
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

    public function register(Request $request, $partner, DeliveryService $delivery_service)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'district' => 'required',
            'thana' => 'required',
            'payment_method' => 'required|in:cheque,beftn,cash,bKash,rocket,nagad',
            'contact_name' => 'required',
            'mobile' => 'required',
            'account_type' => 'required|in:mobile,bank',
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
        $delivery_service->setPartner($partner)
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

        return api_response($request, null, 200, ['messages' => 'আপনার রেজিস্ট্রেশন সফল হয়েছে']);
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
//        $this->validate($request, [
//            'weight' => 'required'
//        ]);

        $partner= $request->partner;
        $this->setModifier($request->manager_resource);


        $charge= $delivery_service->setPartner($partner)->setWeight($request->weight)->setcashOnDelivery($request->cod_amount)->setpickupThana($request->pickupThana)
            ->setpickupDistrict($request->pickupDistrict)->setDeliveryDistrict($request->deliveryDistrict)->setDeliveryThana($request->deliveryThana)->deliveryCharge();

        return api_response($request, null, 200, ['info' => $charge]);

    }

    public function districts(Request $request, $partner, DeliveryService $delivery_service)
    {
        $partner= $request->partner;
        $this->setModifier($request->manager_resource);
        $district = $delivery_service->setPartner($partner)->districts();
        return api_response($request, null, 200, ['district' => $district]);
    }

}
