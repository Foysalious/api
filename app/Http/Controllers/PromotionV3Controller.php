<?php namespace App\Http\Controllers;

use Sheba\Dal\Category\Category;
use App\Models\HyperLocal;
use Sheba\Dal\LocationService\LocationService;
use Illuminate\Http\Request;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\PriceCalculation\PriceCalculationFactory;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;
use Sheba\Voucher\DTO\Params\CheckParamsForOrder;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;
use App\Exceptions\LocationService\LocationServiceNotFoundException;

class PromotionV3Controller extends Controller
{

    public function add($customer, Request $request,
                        DiscountCalculation $discount_calculation, UpsellCalculation $upsell_calculation, ServiceRequest $service_request)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 660);

        $customer = $request->customer;
        $location = $request->location;

        if ($request->has('lat') && $request->has('lng')) {
            $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            $location = $hyper_local ? $hyper_local->location->id : $location;
        }
        $service_requestObjects = $service_request->setServices(json_decode($request->services, 1))->get();
        $price_calculation = $this->resolvePriceCalculation($service_requestObjects[0]->getCategory());
        $order_amount = $this->calculateOrderAmount($price_calculation, $discount_calculation, $upsell_calculation, $location, $service_requestObjects);
        if (!$order_amount) return api_response($request, null, 403);

        $result = voucher($request->code)->check($service_requestObjects[0]->getCategory(), null, $location, $customer, $order_amount, $request->sales_channel)
            ->reveal();

        if ($result['is_valid']) {
            $voucher = $result['voucher'];
            $promotion = new PromotionList($request->customer);
            list($promotion, $msg) = $promotion->add($result['voucher']);
            $promo = array('amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title);

            if ($promotion) return api_response($request, 1, 200, ['promotion' => $promo]); else return api_response($request, null, 403, ['message' => $msg]);
        } else {
            return api_response($request, null, 403, ['message' => $result['message']]);
        }
    }

    public function autoApplyPromotion($customer, Request $request, VoucherSuggester $voucherSuggester, ServiceRequest $serviceRequest, DiscountCalculation $discount_calculation, UpsellCalculation $upsell_calculation)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 660);

        $this->validate($request, ['services' => 'string|required']);
        $service_requestObjects = $serviceRequest->setServices(json_decode($request->services, 1))->get();

        if(empty($service_requestObjects)) return api_response($request, null, 400);

        $location = $request->location;
        $price_calculation = $this->resolvePriceCalculation($service_requestObjects[0]->getCategory());
        if ($request->has('lat') && $request->has('lng')) {
            $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            $location = $hyper_local ? $hyper_local->location->id : $location;
        }

        $order_amount = $this->calculateOrderAmount($price_calculation, $discount_calculation, $upsell_calculation, $location, $service_requestObjects);
        if (!$order_amount) return api_response($request, null, 403, ['message' => 'No partner available at this combination']);

        $order_params = (new CheckParamsForOrder($request->customer, $request->customer->profile))
            ->setApplicant($request->customer)
            ->setCategory($service_requestObjects[0]->getCategory()->id)
            ->setPartner($request->partner)
            ->setLocation((int)$location)
            ->setOrderAmount($order_amount)
            ->setSalesChannel($request->sales_channel);

        $voucherSuggester->init($order_params);
        if ($promo = $voucherSuggester->suggest()) {
            $applied_voucher = [
                'amount' => (int)$promo['amount'],
                'code' => $promo['voucher']->code,
                'id' => $promo['voucher']->id
            ];
            $valid_promos = $this->sortPromotionsByWeight($voucherSuggester->validPromos);
            return api_response($request, $promo, 200, ['voucher' => $applied_voucher, 'valid_promotions' => $valid_promos]);
        } else {
            return api_response($request, null, 404);
        }
    }

    private function calculateOrderAmount($price_calculation, DiscountCalculation $discount_calculation,
                                          UpsellCalculation $upsell_calculation, $location_id, $service_requestObjects)
    {
        $order_amount = 0.00;
        /** @var ServiceRequestObject $service_requestObject */
        foreach ($service_requestObjects as $service_requestObject) {
            $location_service = LocationService::where('service_id', $service_requestObject->getServiceId())->where('location_id', $location_id)->first();
            if (!$location_service) {
                throw new LocationServiceNotFoundException('Service #' . $service_requestObject->getServiceId() . ' is not available at this location', 403);
            }

            if ($location_service->service->isOptions() && !$service_requestObject->getCategory()->isRentACarOutsideCity()) $price_calculation->setLocationService($location_service);

            $price_calculation->setService($service_requestObject->getService())->setOption($service_requestObject->getOption())->setQuantity($service_requestObject->getQuantity());
            $service_requestObject->getCategory()->isRentACarOutsideCity() ? $price_calculation->setPickupThanaId($service_requestObject->getPickupThana()->id)->setDestinationThanaId($service_requestObject->getDestinationThana()->id) : $price_calculation->setLocationService($location_service);
            $upsell_unit_price = $upsell_calculation->setLocationService($location_service)->setOption($service_requestObject->getOption())
                ->setQuantity($service_requestObject->getQuantity())->getUpsellUnitPriceForSpecificQuantity();
            if($upsell_unit_price) $price_calculation->setUpsellUnitPrice($upsell_unit_price);
            $service_amount = $price_calculation->getTotalOriginalPrice();
            $discount_calculation->setService($service_requestObject->getService())->setLocationService($location_service)->setOriginalPrice($service_amount)->setQuantity($service_requestObject->getQuantity())->calculate();
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

    private function resolvePriceCalculation(Category $category)
    {
        $priceCalculationFactory = new PriceCalculationFactory();
        $priceCalculationFactory->setCategory($category);
        return $priceCalculationFactory->get();
    }
}
