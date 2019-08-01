<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Pos\Product\Index;

class PartnerPosController extends Controller
{
    public function getShopProducts($partner, Request $request, Index $product_list)
    {
        try {
            $products = $product_list->setPartnerId((int)$partner)->setIsPublishedForShop(1)->fetch();
            if (count($products) > 0) return api_response($request, $products, 200, ['products' => $products]);
            else return api_response($request, null, 404);
        } catch (\Throwable $e) {
            api_response($request, null, 500);
        }
    }
}