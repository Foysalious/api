<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Sitemap\SitemapCacheRequest;

class SitemapController extends Controller
{
    public function index(Request $request, CacheAside $cacheAside, SitemapCacheRequest $cacheRequest)
    {
        $data = $cacheAside->setCacheRequest($cacheRequest)->getMyEntity();
        dd($data);
        if (!$data) return api_response($request, null, 404);
        return api_response($request, $data, 200, ['master_categories' => $data]);
    }
}