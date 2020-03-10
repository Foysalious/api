<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\PartnerList\Recommended;
use Sheba\ServiceRequest\ServiceRequest;

class CustomerPartnerController extends Controller
{
    public function getPreferredPartners($customer, Request $request, Recommended $recommended, Geo $geo, ServiceRequest $service_request)
    {
        $this->validate($request, [
            'services' => 'required|string',
            'lat' => 'required|numeric', 'lng' => 'required|numeric'
        ]);
        return api_response($request, null, 404);
        $services = json_decode($request->services, 1);
        if (empty($services)) return api_response($request, null, 400);
        $service_request_object = $service_request->setServices($services)->get();
        $geo->setLat($request->lat)->setLng($request->lng);
        $partners = $recommended->setCustomer($request->customer)->setGeo($geo)->setServiceRequestObject($service_request_object)->get();
        if (!$partners) return api_response($request, null, 404);
        return api_response($request, $partners, 200, ['partners' => $partners->map(function (&$partner) {
            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'rating' => round((double)$partner->reviews[0]->avg_rating, 2),
                'logo' => $partner->logo
            ];
        })->values()->all()]);
    }
}
