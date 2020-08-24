<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\Resource;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Partner\Category\CategoryList;
use Sheba\Partner\Service\ServiceList;

class ResourcePartnerController extends Controller
{

    public function getCategories(Request $request, CategoryList $categoryList)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $resource = $this->getResource($request);
        $hyper_local = $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        $categories = $categoryList->setPartner($resource->firstPartner())->setLocationId($hyper_local->location_id)->get();
        return api_response($request, $request, 200, ['categories' => $categories]);
    }

    public function getCategoryServices(Request $request, $category, ServiceList $serviceList)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $resource = $this->getResource($request);
        $hyper_local = $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        $services = $serviceList->setPartner($resource->firstPartner())->setLocationId($hyper_local->location_id)->setCategoryId($category)->get();
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