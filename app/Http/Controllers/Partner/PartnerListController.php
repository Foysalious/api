<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\CustomerDeliveryAddress;
use App\Models\PartnerOrder;
use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\PartnerList\Director;
use Sheba\PartnerList\PartnerListBuilder;
use Sheba\PartnerOrderRequest\Creator;
use Sheba\ServiceRequest\ServiceRequest;

class PartnerListController extends Controller
{
    public function getPartners(Request $request, Geo $geo, PartnerListBuilder $partnerListBuilder, Director $partnerListDirector, ServiceRequest $serviceRequest, Creator $creator)
    {
        $this->validate($request, [
            'services' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|string',
            'address_id' => 'required|numeric',
            'partners' => 'required|string',
            'partner_order_id' => 'required|string'
        ]);
        $partners = json_decode($request->partners, 1);
        $address = CustomerDeliveryAddress::withTrashed()->where('id', (int)$request->address_id)->first();
        $geo->setLng($address->geo->lng)->setLat($address->geo->lat);
        $service_requestObject = $serviceRequest->setServices(json_decode($request->services, 1))->get();
        $partnerListBuilder->setGeo($geo)->setServiceRequestObjectArray($service_requestObject)
            ->setScheduleTime($request->time)->setScheduleDate($request->date);
        if (count($partners) > 0) $partnerListBuilder->setPartnerIdsToIgnore($partners);
        $partnerListDirector->setBuilder($partnerListBuilder)->buildPartnerListForOrderPlacement();
        $partners = $partnerListBuilder->get();
        if (count($partners) > 0) {
            $partner_order = PartnerOrder::find($request->partner_order_id);
            $creator->setPartnerOrder($partner_order)->setPartners($partners->pluck('id')->toArray())->create();
        }
        return api_response($request, $partners, 200);
    }

    public function get(Request $request, Geo $geo, PartnerListBuilder $partnerListBuilder, Director $partnerListDirector, ServiceRequest $serviceRequest)
    {
        $this->validate($request, [
            'services' => 'required|string',
            'date' => 'date_format:Y-m-d',
            'time' => 'string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'partners' => 'string',
        ]);
        $geo->setLng($request->lng)->setLat($request->lat);
        $partners = json_decode($request->partners, 1);
        $service_requestObject = $serviceRequest->setServices(json_decode($request->services, 1))->get();
        $partnerListBuilder->setGeo($geo)->setServiceRequestObjectArray($service_requestObject)->setScheduleTime($request->time)->setScheduleDate($request->date);
        if ($partners) $partnerListBuilder->setPartnerIds($partners);
        $partnerListDirector->setBuilder($partnerListBuilder);
        if ($request->date && $request->time) {
            $partnerListDirector->buildPartnerListForOrderPlacementAdmin();
        } else {
            $partnerListDirector->buildPartnerListForAdmin();
        }
        $partners = $partnerListBuilder->get()->each(function (&$partner) {
            removeRelationsAndFields($partner);
        });
        return api_response($request, $partners, 200, ['partners' => $partners->values()->all(), 'partners_after_conditions' => $partnerListDirector->getPartnerIdsAfterEachCondition()]);
    }
}