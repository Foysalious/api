<?php namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Repository\CategoryRepository;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    Private $categoryRepository;

    public function __construct(CategoryRepository $category_repo)
    {
        $this->categoryRepository = $category_repo;
    }

    public function index(Request $request)
    {
        $partner_id = $request->user->id;
        $products = $this->categoryRepository->getAllMasterCategories($partner_id);
        return api_response($request, null, 200, $products);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);
        $partner_id = $request->partner->id;
        $modifier = $request->manager_resource->profile->name;
        $this->categoryRepository->setModifier($modifier)->setPartner($partner_id)->setCategoryName($request->name)->store();
        return api_response($request, null, 200);
    }




}