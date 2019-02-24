<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        try{
            $categories = Category::whereNotNull('parent_id')->whereHas('services', function($q) {
                $q->whereHas('serviceSubscription',function($query) {
                    return $query->whereNotNull('id');
                });
            })->with(['services'=>function($q){
                $q->whereHas('serviceSubscription',function($query) {
                    return $query->whereNotNull('id');
                });
                $q->with('serviceSubscription');
            }])->get();

            $parents = collect();
            foreach ($categories as $category) {
                $parent =[
                    'id'=>$category->parent->id,
                    'name'=> $category->parent->name,
                    'bn_name' => $category->parent->bn_name,
                    'slug' => $category->parent->slug,
                    'short_description' => $category->parent->slug,
                    'subscriptions' =>  $category->services->map(function($service){
                        list($service['max_price'], $service['min_price']) = $this->getPriceRange($service);
                        $subscription = $service->serviceSubscription;
                        $subscription['max_price'] = $service['max_price'];
                        $subscription['min_price'] = $service['min_price'];
                        return $subscription;
                    }),
                ];
                $parents->push($parent);
            }

            return api_response($request, $category, 200, ['category' => $parents]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    private function getPriceRange(Service $service)
    {
        try {
            $max_price = [];
            $min_price = [];
            if ($service->partners->count() == 0) return array(0, 0);
            foreach ($service->partners->where('status', 'Verified') as $partner) {
                $partner_service = $partner->pivot;
                if (!($partner_service->is_verified && $partner_service->is_published)) continue;
                $prices = (array)json_decode($partner_service->prices);
                $max = max($prices);
                $min = min($prices);
                array_push($max_price, $max);
                array_push($min_price, $min);
            }
            return array((double)max($max_price) * $service->min_quantity, (double)min($min_price) * $service->min_quantity);
        } catch (\Throwable $e) {
            return array(0, 0);
        }
    }
}
