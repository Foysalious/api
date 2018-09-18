<?php namespace App\Http\Controllers;

use App\Models\RewardShopProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\RewardShop\OrderHandler;
use Sheba\RewardShop\OrderValidator;

class RewardShopController extends Controller
{
    use ModificationFields;

    public function index(Request $request)
    {
        try{
            $products = RewardShopProduct::published()->select('id', 'name', 'thumb', 'point')->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->thumb,
                    'required_point' => $product->point
                ];
            });
            return api_response($request, $products, 200, ['products' => $products, 'gift_points' => $request->partner->reward_point]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function purchase(Request $request, OrderValidator $validator)
    {
        try {
            $this->validate($request, [
                'product_id' => 'required|exists:reward_products,id'
            ]);
            $product = RewardShopProduct::find($request->product_id);

            $is_valid = $validator->canPurchase($product, $request->partner);
            if ($is_valid) {
                $this->setModifier($request->manager_resource);
                (new OrderHandler())->create($product, $request->partner);
                return api_response($request, null, 200);
            }
            return api_response($request, null, 400, ['message' => "You can't purchase this product"]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
