<?php namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\PartnerPosService;
use App\Models\PosCategory;
use App\Models\TopUpOrder;
use App\Transformers\Partner\PosServiceTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Dal\PartnerPosService\PartnerPosServiceRepository;
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
                $category_data= [];
                foreach ($categories as $key => $value) {
                    $category['id'] = $value;
                    $category['name'] = $key;
                    $category['total_products'] = collect($fractal->createData($resource)->toArray()['data'])->where('category_id',$value)->count();
                    array_push($category_data, $category);
                }
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

    public function search(Request $request, PartnerPosServiceRepository $partnerPosServiceRepository)
    {
        $this->validate($request, ['search' => 'required|string', 'partner_id' => 'required|numeric']);
        $products = $partnerPosServiceRepository->searchProductFromWebstore($request->search, +$request->partner_id, 5);
        if (count($products->toArray()) > 0) return response()->json(['products' => $products->toArray()]);
        return response("No products found", 404);
    }

}