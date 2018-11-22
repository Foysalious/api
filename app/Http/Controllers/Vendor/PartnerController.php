<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class PartnerController extends Controller
{
    use Helpers;

    public function getPartners(Request $request)
    {
        $this->validate($request, [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'services' => 'required'
        ]);
        $location = $this->api->get('v2/locations/current?lat=' . $request->lat . '&lng=' . $request->lng);
        $partners = $this->api->get('v2/locations/' . $location->id . '/partners?services=' . $request->services . '&skip_availability=1');
        if ($partners) {
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($partners, new TimeTransformer());
        }
    }
}