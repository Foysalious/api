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
        $partner_id = $request->partner->id;
        $modifier = $request->manager_resource->profile->name;
        $this->categoryService->setModifier($modifier)->setPartner($partner_id)->setCategoryName($request->name)->store();
        return api_response($request, null, 200);
    }

}