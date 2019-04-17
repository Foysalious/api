<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\PartnerPosService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            $services = [];
            $base_query = PartnerPosService::with('discounts');

            if ($request->has('category_id') && !empty($request->category_id)) {
                $category_ids = explode(',', $request->category_id);
                $base_query->whereIn('pos_category_id', $category_ids);
            }

            $base_query->select($this->getSelectColumnsOfService())
                ->partner($partner->id)->get()
                ->each(function ($service) use (&$services) {
                    $services[] = [
                        'name'                  => $service->name,
                        'app_thumb'             => $service->app_thumb,
                        'app_banner'            => $service->app_banner,
                        'price'                 => $service->price,
                        'stock'                 => $service->stock,
                        'discount_applicable'   => $service->discount() ? true : false,
                        'discounted_price'      => $service->discount() ? $service->getDiscountedAmount() : 0
                    ];
            });
            if (!$services) return api_response($request, null, 404);

            return api_response($request, $services, 200, ['services' => $services]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request)
    {
        $service = [
            'name' => 'Flower Kit',
            'image' => 'https://lh6.googleusercontent.com/proxy/u8eiKX1ZnFRzptzjIcyehRP-wH1_GbB9P0uog4dh5wrsXmx57m7H97yRAjjTmqSaAWwtWGHyYqI9dFYsvL-L75RrMF_bIeUPmgwqRjAtRWop_PrcMNXoeUcHWfdqvLuzPURmnYlAOSeZYOcOpyYrDYpXleM=w100-h134-n-k-no',
            'regular_price' => 1300.00,
            'discounted_price' => 1254.00,
            'category' => 'Foods',
            'sub_category' => 'Breakfast',
            'inventory' => true,
            'quantity' => 300,
            'purchase_cost' => 20,
            'vat_applicable' => true,
            'vat' => 0.20,
            'discount_applicable' => true,
            'discount_amount' => 46,
            'discount_end_time' => Carbon::parse('11-08-2019')
        ];
        try {
            return api_response($request, $service, 200, ['service' => $service]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfService()
    {
        return ['id', 'name', 'app_thumb', 'app_banner', 'price', 'stock'];
    }
}
