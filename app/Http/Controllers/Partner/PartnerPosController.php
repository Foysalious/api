<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Pos\Product\Index;
use Sheba\Repositories\PartnerRepository;

class PartnerPosController extends Controller
{
    public function getShopProducts($partner, Request $request, Index $product_list, PartnerRepository $partner_repository)
    {
        try {
            $products = $product_list->setPartnerId((int)$partner)->setIsPublishedForShop(1);
            if (is_numeric($partner)) $partner = $partner_repository->find($partner);
            else $products = $products->setPartnerSlug();
            $products = $products->fetch();;
            if (count($products) > 0) return api_response($request, $products, 200, ['products' => $products]);
            else return api_response($request, null, 404);
        } catch (\Throwable $e) {
            api_response($request, null, 500);
        }
    }
}