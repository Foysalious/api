<?php namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryGroupCategory;
use App\Models\HyperLocal;
use App\Transformers\Category\CategoryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\CategoryChildrenCacheRequest;
use Sheba\Cache\Category\Info\CategoryCacheRequest;
use Sheba\Cache\Category\Tree\CategoryTreeCache;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Dal\UniversalSlug\SluggableType;
use League\Fractal\Resource\Item;

class CategoryController extends Controller
{
    public function show($category, Request $request, CacheAside $cacheAside, CategoryCacheRequest $cacheRequest)
    {
        $cacheRequest->setCategoryId($category);
        $data = $cacheAside->setCacheRequest($cacheRequest)->getMyEntity();
        if (!$data) return api_response($request, null, 404);
        return api_response($request, $data, 200, ['category' => $data]);
    }

    public function getSecondaries($category, Request $request, CacheAside $cacheAside, CategoryChildrenCacheRequest $cacheRequest)
    {
        $this->validate($request, ['lat' => 'numeric', 'lng' => 'numeric', 'location_id' => 'required']);
        $cacheRequest->setCategoryId($category)->setLocationId($request->location_id);
        $data = $cacheAside->setCacheRequest($cacheRequest)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }

    public function getCategoryTree(Request $request, CacheAside $cacheAside, CategoryTreeCacheRequest $categoryTreeCache)
    {
        $this->validate($request, ['location_id' => 'required|numeric']);
        $categoryTreeCache->setLocationId($request->location_id);
        $data = $cacheAside->setCacheRequest($categoryTreeCache)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }

    public function getServicesOfChildren(Request $request, CacheAside $cacheAside){


    }
}
