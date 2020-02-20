<?php namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Location\LocationCache;

class LocationController extends Controller
{
    public function index(Request $request, CacheAside $cache_aside, LocationCache $location_cache)
    {
        return api_response($request, true, 200, $cache_aside->setCacheObject($location_cache)->getMyEntity());
    }

}