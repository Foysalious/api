<?php namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Repository\ProductRepositry;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    Private $productRepository;

    public function __construct(ProductRepositry $product_repo)
    {
        $this->productRepository = $product_repo;
    }

    public function index(Request $request)
    {
        $partner = $request->partner;
        $products = $this->productRepository->getAllProducts($partner->id);
        return api_response($request, null, 200, $products);
    }

}