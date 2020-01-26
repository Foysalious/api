<?php namespace App\Http\Controllers;

use App\Models\HyperLocal;
use App\Models\LocationService;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\Voucher\PromotionList;
use Throwable;

class PromotionV3Controller extends Controller
{
    /**
     * @param $customer
     * @param Request $request
     * @param PriceCalculation $price_calculation
     * @param DiscountCalculation $discount_calculation
     * @return JsonResponse
     */
    public function add($customer, Request $request, PriceCalculation $price_calculation,
                        DiscountCalculation $discount_calculation)
    {
        try {
            $customer = $request->customer;
            $location = $request->location;

            if ($request->has('lat') && $request->has('lng')) {
                $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                $location = $hyper_local ? $hyper_local->location->id : $location;
            }

            $order_amount = $this->calculateOrderAmount($price_calculation, $discount_calculation, $request->services, $location);
            if (!$order_amount) return api_response($request, null, 403);
            $category = Service::find(json_decode($request->services)[0]->id)->category_id;

            $result = voucher($request->code)->check($category, null, $location, $customer, $order_amount, $request->sales_channel)->reveal();

            if ($result['is_valid']) {
                $voucher = $result['voucher'];
                $promotion = new PromotionList($request->customer);
                list($promotion, $msg) = $promotion->add($result['voucher']);
                $promo = array('amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title);

                if ($promotion) return api_response($request, 1, 200, ['promotion' => $promo]); else return api_response($request, null, 403, ['message' => $msg]);
            } else {
                return api_response($request, null, 403, ['message' => 'Invalid Promo']);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param PriceCalculation $price_calculation
     * @param DiscountCalculation $discount_calculation
     * @param $services
     * @param $location_id
     * @return float|mixed
     */
    private function calculateOrderAmount(PriceCalculation $price_calculation, DiscountCalculation $discount_calculation, $services, $location_id)
    {
        $order_amount = 0.00;
        foreach (json_decode($services) as $selected_service) {
            $location_service = LocationService::where('service_id', $selected_service->id)->where('location_id', $location_id)->first();
            if ($location_service->service->isOptions()) $price_calculation->setLocationService($location_service);

            $price_calculation->setLocationService($location_service)->setOption($selected_service->option)->setQuantity($selected_service->quantity);
            $discount_calculation->setLocationService($location_service)->setOriginalPrice($price_calculation->getTotalOriginalPrice())->calculate();

            $order_amount += $discount_calculation->getDiscountedPrice();
        }
        return $order_amount;
    }
}
