<?php namespace App\Http\Controllers\CategoryGroup;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\CategoryGroup\CategoryGroupCache;

class CategoryGroupController extends Controller
{
    public function index(Request $request, CacheAside $cacheAside, CategoryGroupCache $category_group_cache)
    {
        $this->validate($request, ['location_id' => 'numeric|required', 'name' => 'required|string|in:trending']);
        $category_group_cache->setLocationId(4)->setCategoryGroupId(config('sheba.category_groups')[$request->name]);
        return api_response($request, 1, 200, $cacheAside->setCacheObject($category_group_cache)->getMyEntity());
    }
}