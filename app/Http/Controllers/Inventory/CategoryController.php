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
        $partner = $request->partner;
        $products = $this->categoryRepository->getAllMasterCategories($partner->id);
        return api_response($request, null, 200, $products);
    }




}