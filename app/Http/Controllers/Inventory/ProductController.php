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
        $category_products = $this->productService
            ->setCategoryIds($request->category_ids)
            ->setSubCategoryIds($request->sub_category_ids)
            ->setUpdatedAfter($request->updated_after)
            ->setIsPublishedForWebstore($request->is_published_for_webstore)
            ->setOffset($request->offset)
            ->setLimit($request->limit)
            ->getProducts($partner->id);
        return http_response($request, null, 200, $category_products);
    }

    public function getWebstoreProducts(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $products = $this->productService->setOffset($request->offset)->setLimit($request->limit)->setSearchKey($request->q)->getWebstoreProducts($partner->id);
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
            ->setSubCategoryId($request->sub_category_id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setWarranty($request->warranty)
            ->setWarrantyUnit($request->warranty_unit)
            ->setVatPercentage($request->vat_percentage)
            ->setUnitId($request->unit_id)
            ->setAppThumb($request->file('app_thumb'))
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
            ->setSubCategoryId($request->sub_category_id)
            ->setName($request->name)
            ->setDescription($request->description)
            ->setWarranty($request->warranty)
            ->setWarrantyUnit($request->warranty_unit)
            ->setVatPercentage($request->vat_percentage)
            ->setUnitId($request->unit_id)
            ->setProductDetails($request->product_details)
            ->setDeletedImages($request->deleted_images)
            ->setAppThumb($request->file('app_thumb'))
            ->setImages($request->file('images'))
            ->update();
        return http_response($request, null, 200, $product);
    }

    public function destroy(Request $request, $productId)
    {
        $partner = $request->auth_user->getPartner();
        $product = $this->productService
            ->setPartnerId($partner->id)
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

    public function addStock(Request $request, $productId)
    {
        $partner = $request->auth_user->getPartner();
        $product = $this->productService->setPartnerId($partner->id)->setProductId($productId)->setStock($request->stock)
            ->setSkuId($request->sku_id)->setAccountingInfo($request->accounting_info)->setCost($request->cost)->addStock();
        return http_response($request, null, 200, $product);
    }

    public function changePublishStatus(Request $request,$product_id,$status)
    {
        $partner = $request->auth_user->getPartner();
        $this->productService->setPartnerId($partner->id)->setProductId($product_id)->setPublishStatus($status)->changePublishStatus();
        return http_response($request, null, 200);
    }

}