<?php namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\CategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $categoryService;

    public function __construct(CategoryService $category_service)
    {
        $this->categoryService = $category_service;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $products = $this->categoryService->getAllMasterCategories($partner->id);
        return http_response($request, null, 200, $products);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function createSubCategory(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->categoryService
            ->setPartner($partner->id)
            ->setCategoryName($request->name)
            ->setThumb($request->thumb)
            ->setParentId($request->parent_id)
            ->createSubCategory();
        return http_response($request, null, 201, $response);
    }

    public function update(Request $request, $category_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->categoryService->setPartner($partner->id)
            ->setCategoryId($category_id)
            ->setCategoryName($request->name)
            ->setThumb($request->thumb)
            ->update();
        return http_response($request, null, 200, $response);
    }

    public function delete(Request $request, $category_id)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->categoryService->setPartner($partner->id)->setCategoryId($category_id)->setCategoryName($request->name)->delete();
        return http_response($request, null, 200, $response);
    }

    public function allCategory(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $categories = $this->categoryService->setUpdatedAfter($request->updated_after)->getallcategory($partner->id);
        return http_response($request, null, 200, $categories);
    }

    public function createCategory(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->categoryService
            ->setPartner($partner->id)
            ->setCategoryName($request->category_name)
            ->setThumb($request->category_thumb)
            ->setSubCategories($request->sub_category)
            ->storeCategoryWithSubCategory();
        return http_response($request, null, 201, $response);
    }

    public function show(Request $request, int $category_id)
    {
        $partner = $request->auth_user->getPartner();
        $details = $this->categoryService->setCategoryId($category_id)->setPartner($partner->id)->getCategoryDetail();
        return http_response($request, null, 201, $details);
    }

    public function getPartnerCategory(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $categoryList = $this->categoryService->getPartnerWiseCategoryList($partner->id);
        return http_response($request, null, 200, $categoryList);
    }

}
