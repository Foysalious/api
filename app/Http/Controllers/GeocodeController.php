<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Location\Geo;
use Sheba\ReverseGeoCode\Address;
use Sheba\ReverseGeoCode\Barikoi\BarikoiClient;
use Sheba\ReverseGeoCode\ReverseGeoCode;

class GeocodeController extends Controller
{
    public function reverseGeocode(Request $request, ReverseGeoCode $reverse_geoCode, BarikoiClient $barikoi_client, Geo $geo)
    {
        try {
            $geo->setLat($request->lat)->setLng($request->lng);
            /** @var Address $address */
            $address = $reverse_geoCode->setClient($barikoi_client)->setGeo($geo)->get();
            if (!$address->hasAddress()) return api_response($request, null, 404);
            return api_response($request, $address, 200, ['location' => ['address' => $address->getAddress()]]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}