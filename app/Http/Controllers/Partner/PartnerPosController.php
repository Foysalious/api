<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PosCategory;
use App\Transformers\Partner\PosServiceTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Pos\Product\Index;
use Sheba\Pos\Repositories\Interfaces\PosCategoryRepositoryInterface;

class PartnerPosController extends Controller
{
    public function getProducts($partner, Request $request, Index $product_list, Manager $fractal, PosCategoryRepositoryInterface $posCategoryRepository)
    {
        try {
            $is_shop = $request->is_shop ? $request->is_shop : 0;
            $products = $product_list->setIsPublishedForShop($is_shop);
            $products = is_numeric($partner) ? $products->setPartnerId((int)$partner) : $products->setPartnerSlug($partner);
            $products = $products->getAvailableProducts();
            if (count($products) > 0) {

                $categories = PosCategory::parents()->published()->select(['id','name'])
                    ->whereHas('children',function ($q) use ($products) {
                        $q->whereIn('id', $products->pluck('pos_category_id')->unique()->toArray());
                    })->get();

                $resource = new Collection($products, new PosServiceTransformer());
                return api_response($request, $products, 200, ['products' => $fractal->createData($resource)->toArray()['data'],
                    'categories' => $categories->map(function ($category) use ($products) {
                        $category['total_products'] = $products->whereIn('pos_category_id', $category->children->pluck('id')->toArray())->count();
                        removeRelationsAndFields($category);
                        return $category;
                    })]);
            } else return api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}