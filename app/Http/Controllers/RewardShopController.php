<?php namespace App\Http\Controllers;

use App\Models\RewardShopProduct;
use Illuminate\Http\Request;

class RewardShopController extends Controller
{
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
}
