<?php namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\Resource;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Location\Geo;
use Sheba\Partner\Category\CategoryList;
use Sheba\Resource\Service\ServiceList;

class ResourcePartnerController extends Controller
{

    public function getCategories(Request $request, CategoryList $categoryList)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $resource = $this->getResource($request);
        $hyper_local = $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        if (!$hyper_local) return api_response($request, null, 404, ['message' => "You're outside our service area."]);
        $categories = $categoryList->setPartner($resource->firstPartner())->setLocationId($hyper_local->location_id)->get();
        return api_response($request, $request, 200, ['categories' => $categories]);
    }

    public function getCategoryServices(Request $request, $category, ServiceList $serviceList, Geo $geo)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->first();
        if (!$hyper_local) return api_response($request, null, 404, ['message' => "You're outside our service area."]);
        $resource = $this->getResource($request);
        $geo->setLat($request->lat)->setLng($request->lng);
        $services = $serviceList->setResource($resource)->setCategoryId($category)->setGeo($geo)->getAllServices();
        return api_response($request, $request, 200, ['services' => $services]);
    }

    /**
     * @param Request $request
     * @return Resource|null
     */
    private function getResource(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        return $auth_user->getResource();
    }
}