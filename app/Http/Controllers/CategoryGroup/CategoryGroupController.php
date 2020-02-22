<?php namespace App\Http\Controllers\CategoryGroup;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\CategoryGroup\CategoryGroupCache;
use Sheba\Cache\CategoryGroup\CategoryGroupCacheRequest;

class CategoryGroupController extends Controller
{
    public function index(Request $request, CacheAside $cacheAside, CategoryGroupCacheRequest $category_group_cache)
    {
        $this->validate($request, ['location_id' => 'numeric|required', 'name' => 'required|string|in:trending']);
        $category_group_cache->setLocationId($request->location_id)->setCategoryGroupId(config('sheba.category_groups')[$request->name]);
        $data = $cacheAside->setCacheRequest($category_group_cache)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }
}