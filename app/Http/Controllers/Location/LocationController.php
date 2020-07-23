<?php namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Location\LocationCache;
use Sheba\Cache\Location\LocationCacheRequest;
use Sheba\Location\FromGeo;

class LocationController extends Controller
{
    public function index(Request $request, CacheAside $cache_aside, LocationCacheRequest $location_cache_request)
    {
        $data = $cache_aside->setCacheRequest($location_cache_request)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }

    public function getThanaFromLatLng(Request $request, FromGeo $fromGeo)
    {
        try {
            $this->validate($request, [
                'lat' => 'required',
                'lng' => 'required',
            ]);
            $lat = $request->lat;
            $lng = $request->lng;
            $thana = $fromGeo->setThanas()->getThana($lat, $lng);
            if(!$thana) return api_response($request, [], 404, ['message' => 'Thana not found.']);

            return api_response($request, null, 200, ['thana' => [
                'id' => $thana->id,
                'name' => $thana->name,
                'location_id' => $thana->location_id,
                'lat' => $thana->lat,
                'lng' => $thana->lng,
            ]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        }
    }

}