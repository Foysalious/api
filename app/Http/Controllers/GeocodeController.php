<?php namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Barikoi\BarikoiClient;
use Sheba\Location\Geo;

class GeocodeController extends Controller
{

    public function reverseGeocode(Request $request)
    {
        $b = new BarikoiClient();
        $geo = new Geo();
        $geo->setLat($request->lat)->setLng($request->lng);
        $address = $b->getAddress($geo);
        if (!$address) return api_response($request, 500, null);
        if ($address) return api_response($request, 200, $address,
            [
                'location' => ['address' => $address]
            ]
        );
    }
}