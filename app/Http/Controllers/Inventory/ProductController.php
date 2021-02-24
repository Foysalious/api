<?php namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    Private $productService;

    public function __construct(ProductService $product_service)
    {
        $this->productService = $product_service;
    }

    public function index(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $products = $this->productService->getAllProducts($partner->id);
        return api_response($request, null, 200, $products);
    }

}