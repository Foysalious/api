<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Barikoi\BarikoiClient;
use Sheba\Location\Geo;

class GeocodeController extends Controller
{
    public function reverseGeocode(Request $request)
    {
        try {
            $b = new BarikoiClient();
            $geo = new Geo();
            $geo->setLat($request->lat)->setLng($request->lng);
            $address = $b->getAddress($geo);
            if (!$address) return api_response($request, null, 404);
            if ($address) return api_response($request, $address, 200, ['location' => ['address' => $address]]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}