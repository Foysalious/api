<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
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

    public function getVendorList(Request $request,DeliveryService $delivery_service)
    {
        $vendor= $delivery_service->vendorlist();
        return api_response($request, null, 200, ['delivery_vendor' => $vendor]);

    }

    public function getOrderInformation(Request $request, $partner,$order_id ,DeliveryService $delivery_service)
    {

        $partner = $request->partner;

        $this->setModifier($request->manager_resource);

        $order_information = $delivery_service->setPartner($partner)->getOrderInfo($order_id);
        return api_response($request, null, 200, ['order_information' => $order_information]);

    }

}
