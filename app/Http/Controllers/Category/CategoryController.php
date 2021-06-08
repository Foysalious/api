<?php namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use Illuminate\Http\Request;
use Sheba\Cache\CacheAside;
use Sheba\Cache\Category\Children\CategoryChildrenCacheRequest;
use Sheba\Cache\Category\Children\Services\ServicesCacheRequest;
use Sheba\Cache\Category\Info\CategoryCacheRequest;
use Sheba\Cache\Category\Tree\CategoryTreeCacheRequest;
use Sheba\Dal\Category\Category;

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
        $location_id = $this->getLocation($request->location_id, $request->lat, $request->lng);
        if (!$location_id) return api_response($request, 1, 404);
        $cacheRequest->setCategoryId($category)->setLocationId($location_id);
        $data = $cacheAside->setCacheRequest($cacheRequest)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }

    public function getCategoryTree(Request $request, CacheAside $cacheAside, CategoryTreeCacheRequest $categoryTreeCache)
    {
        $this->validate($request, ['location_id' => 'numeric', 'lat' => 'numeric', 'lng' => 'numeric']);
        $location_id = $this->getLocation($request->location_id, $request->lat, $request->lng);
        if (!$location_id) return api_response($request, 1, 404);
        $categoryTreeCache->setLocationId($location_id);
        $data = $cacheAside->setCacheRequest($categoryTreeCache)->getMyEntity();
        if (!$data) return api_response($request, 1, 404);
        return api_response($request, 1, 200, $data);
    }

    public function getServicesOfChildren($category, Request $request, CacheAside $cacheAside, ServicesCacheRequest $cacheRequest)
    {
        $this->validate($request, ['location_id' => 'numeric', 'lat' => 'numeric', 'lng' => 'numeric']);
        $location_id = $this->getLocation($request->location_id, $request->lat, $request->lng);
        if (!$location_id) return api_response($request, 1, 404);
        $cacheRequest->setLocationId($location_id)->setCategoryId($category);
        $data = $cacheAside->setCacheRequest($cacheRequest)->getMyEntity();
        if (!$data) return api_response($request, null, 404);
        return api_response($request, 1, 200, $data);

    }

    /**
     * @param $location_id
     * @param $lat
     * @param $lng
     * @return int|null
     */
    private function getLocation($location_id, $lat, $lng)
    {
        if ($location_id) {
            return (int)$location_id;
        } elseif ($lat && $lng) {
            $hyperLocation = HyperLocal::insidePolygon((double)$lat, (double)$lng)->first();
            if (!$hyperLocation) return null;
            return $hyperLocation->location_id;
        }
        return null;
    }

    public function getSuggestions(Request $request)
    {
        $categories = Category::where('parent_id', '<>', 'null')->select('id', 'name', 'bn_name')->get();

        return count($categories) > 0 ? api_response($request, $categories, 200, ['categories' => $categories]) : api_response($request, null, 404);
    }
}
