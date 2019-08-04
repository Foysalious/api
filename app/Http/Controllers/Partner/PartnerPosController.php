<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Pos\Product\Index;
use Sheba\Repositories\PartnerRepository;

class PartnerPosController extends Controller
{
    public function getProducts($partner, Request $request, Index $product_list, PartnerRepository $partner_repository)
    {
        try {
            $is_shop = $request->is_shop ? $request->is_shop : 0;
            $products = $product_list->setPartnerId((int)$partner)->setIsPublishedForShop($is_shop);
            if (is_numeric($partner)) $partner = $partner_repository->find($partner);
            else $products = $products->setPartnerSlug($partner);
            $products = $products->fetch();;
            if (count($products) > 0) return api_response($request, $products, 200, ['products' => $products]);
            else return api_response($request, null, 404);
        } catch (\Throwable $e) {
            api_response($request, null, 500);
        }
    }
}