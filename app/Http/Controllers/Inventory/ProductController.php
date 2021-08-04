<?php namespace App\Http\Controllers\Inventory;


use App\Http\Controllers\Controller;
use App\Sheba\InventoryService\Services\ProductService;
use Exception;
use Illuminate\Http\JsonResponse;
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
        return http_response($request, null, 200, $products);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $response = $this->productService
            ->setPartnerId($partner->id)
            ->setCategoryId($request->category_id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setWarranty($request->warranty)
            ->setWarrantyUnit($request->warranty_unit)
            ->setVatPercentage($request->vat_percentage)
            ->setUnitId($request->unit_id)
            ->setImages($request->file('images'))
            ->setWholesalePrice($request->wholesale_price)
            ->setCost($request->cost)
            ->setPrice($request->price)
            ->setStock($request->stock)
            ->setChannelId($request->channel_id)
            ->setDisCountAmount($request->discount_amount)
            ->setDiscountEndDate($request->discount_end_date)
            ->setProductDetails($request->product_details)
            ->store();
        return http_response($request, null, 200, $response);
    }

    public function show(Request $request, $productId)
    {
        $partner = $request->auth_user->getPartner();
        $product = $this->productService->setPartnerId($partner->id)->setProductId($productId)->getDetails();
        return http_response($request, null, 200, $product);
    }

    public function update(Request $request, $productId)
    {
        $partner = $request->auth_user->getPartner();
        $product = $this->productService
            ->setPartnerId($partner->id)
            ->setProductId($productId)
            ->setCategoryId($request->category_id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setWarranty($request->warranty)
            ->setWarrantyUnit($request->warranty_unit)
            ->setVatPercentage($request->vat_percentage)
            ->setUnitId($request->unit_id)
            ->setProductDetails($request->product_details)
            ->update();
        return http_response($request, null, 200, $product);
    }

    public function destroy(Request $request, $partnerId, $productId)
    {
        $product = $this->productService
            ->setPartnerId($partnerId)
            ->setProductId($productId)
            ->delete();
        return http_response($request, null, 200, $product);
    }

    public function getLogs(Request $request, $productId)
    {
        $partner = $request->auth_user->getPartner();
        $product = $this->productService->setPartnerId($partner->id)->setProductId($productId)->getLogs();
        return http_response($request, null, 200, $product);
    }

}