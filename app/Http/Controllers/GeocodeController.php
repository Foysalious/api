<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\Map\Address;
use Sheba\Map\ReverseGeoCode;

class GeocodeController extends Controller
{
    public function reverseGeocode(Request $request, ReverseGeoCode $reverse_geoCode, Geo $geo)
    {
        $geo->setLat($request->lat)->setLng($request->lng);
        /** @var Address $address */
        $address = $reverse_geoCode->setGeo($geo)->getAddress();
        if (!$address->hasAddress()) return api_response($request, null, 404);
        return api_response($request, $address, 200, ['location' => ['address' => $address->getAddress()]]);
    }
}