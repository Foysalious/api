<?php namespace App\Http\Controllers;

use App\Models\HyperLocal;
use App\Models\LocationService;
use App\Models\Service;
use App\Sheba\Checkout\PartnerList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\Voucher\DTO\Params\CheckParamsForOrder;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;
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
     * @param $customer
     * @param Request $request
     * @param VoucherSuggester $voucherSuggester
     * @param PartnerListRequest $partnerListRequest
     * @param PriceCalculation $price_calculation
     * @param DiscountCalculation $discount_calculation
     * @return JsonResponse
     */
    public function autoApplyPromotion($customer, Request $request, VoucherSuggester $voucherSuggester, PartnerListRequest $partnerListRequest,
                                       PriceCalculation $price_calculation, DiscountCalculation $discount_calculation)
    {
        try {
            $partnerListRequest->setRequest($request)->prepareObject();
            $location = $request->location;

            if ($request->has('lat') && $request->has('lng')) {
                $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                $location = $hyper_local ? $hyper_local->location->id : $location;
            }

            $order_amount = $this->calculateOrderAmount($price_calculation, $discount_calculation, $request->services, $location);
            if (!$order_amount) return api_response($request, null, 403, ['message' => 'No partner available at this combination']);

            $order_params = (new CheckParamsForOrder($request->customer, $request->customer->profile))
                ->setApplicant($request->customer)
                ->setCategory($partnerListRequest->selectedCategory->id)
                ->setPartner($request->partner)
                ->setLocation((int)$location)
                ->setOrderAmount($order_amount)
                ->setSalesChannel($request->sales_channel);

            $voucherSuggester->init($order_params);

            if ($promo = $voucherSuggester->suggest()) {
                $applied_voucher = array('amount' => (int)$promo['amount'], 'code' => $promo['voucher']->code, 'id' => $promo['voucher']->id);
                $valid_promos = $this->sortPromotionsByWeight($voucherSuggester->validPromos);
                return api_response($request, $promo, 200, ['voucher' => $applied_voucher, 'valid_promotions' => $valid_promos]);
            } else {
                return api_response($request, null, 404);
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

    /**
     * @param $valid_promos
     * @return mixed
     */
    private function sortPromotionsByWeight($valid_promos)
    {
        return $valid_promos->map(function ($promotion) {
            $promo = [];
            $promo['id'] = $promotion['voucher']->id;
            $promo['title'] = $promotion['voucher']->title;
            $promo['amount'] = (double)$promotion['amount'];
            $promo['code'] = $promotion['voucher']->code;
            $promo['priority'] = round($promotion['weight'], 4);
            return $promo;
        })->sortByDesc(function ($promotion) {
            return $promotion['priority'];
        })->values()->all();
    }
}
