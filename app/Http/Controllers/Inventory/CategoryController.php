<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    Private $categoryService;

    public function __construct(CategoryService $category_service)
    {
        $this->categoryService = $category_service;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $products = $this->categoryService->getAllMasterCategories($partner->id);
        return api_response($request, null, 200, $products);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $modifier = $request->auth_user->getResource()->profile->name;
        $response = $this->categoryService->setModifier($modifier)->setPartner($partner->id)->setCategoryName($request->name)->store();
        return api_response($request, null, 200, $response);
    }

    public function update(Request $request,$partner_id,$category_id)
    {
        $partner = $request->auth_user->getPartner();
        $modifier = $request->auth_user->getResource()->profile->name;
        $response =  $this->categoryService->setModifier($modifier)->setPartner($partner->id)->setCategoryId($category_id)->setCategoryName($request->name)->update();
        return api_response($request, null, 200, $response);
    }

}