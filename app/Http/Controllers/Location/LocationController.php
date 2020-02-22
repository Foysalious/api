<?php namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Location\LocationCache;
use Sheba\Cache\Location\LocationCacheRequest;

class LocationController extends Controller
{
    public function index(Request $request, CacheAside $cache_aside, LocationCacheRequest $location_cache_request)
    {
        return api_response($request, true, 200, $cache_aside->setCacheRequest($location_cache_request)->getMyEntity());
    }

}