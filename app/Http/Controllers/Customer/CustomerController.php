<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LocationService;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;

class CustomerController extends Controller
{
    public function getOrderAgain($customer, Request $request, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                                  JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation)
    {
        $customer = $request->customer;
        $reviews = Review::where([['customer_id', $customer->id], ['rating', '>=', 4]])->select('id', 'category_id', 'job_id', 'rating', 'partner_id')
            ->with(['category' => function ($q) {
                $q->select('id', 'name', 'thumb', 'app_thumb', 'banner', 'app_banner', 'frequency_in_days');
            }, 'job' => function ($q) {
                $q->select('id', 'category_id', 'partner_order_id')->with('category')->with(['jobServices' => function ($q) {
                    $q->select('id', 'job_id', 'service_id', 'quantity', 'option', 'variable_type')->with(['service' => function ($q) {
                        $q->select('id', 'min_quantity', 'thumb', 'app_thumb', 'banner', 'app_banner');
                    }]);
                }, 'partnerOrder' => function ($q) {
                    $q->select('id', 'order_id', 'partner_id')->with(['order' => function ($q) {
                        $q->select('id', 'location_id');
                    }]);
                }]);
            }])->orderBy('id', 'desc');
        if ($request->has('category_id')) $reviews = $reviews->where('category_id', $request->category_id);
        $reviews = $reviews->get();
        if (count($reviews) == 0) return api_response($request, null, 404);
        $reviews = $reviews->unique('category_id');
        $final = [];
        foreach ($reviews as $review) {
            if ($review->job->jobServices->count() == 0) continue;
            $data = [];
            $data['category'] = $review->category;
            $data['category']['delivery_charge'] = $delivery_charge->setCategory($review->category)->get();
            $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($data['category']['delivery_charge']);
            $job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($review->category)->setCheckingParams($discount_checking_params)->calculate();
            /** @var Discount $delivery_discount */
            $delivery_discount = $job_discount_handler->getDiscount();
            $data['category']['delivery_discount'] = $delivery_discount ? [
                'value' => (double)$delivery_discount->amount,
                'is_percentage' => $delivery_discount->is_percentage,
                'cap' => (double)$delivery_discount->cap,
                'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
            ] : null;
            $all_services = [];
            foreach ($review->job->jobServices as $job_service) {
                $service = $job_service->service;
                /** @var LocationService $location_service */
                $location_service = LocationService::where('location_id', $review->job->partnerOrder->order->location_id)->where('service_id', $job_service->service_id)->first();
                if (!$location_service) continue;
                /** @var ServiceDiscount $discount */
                $discount = $location_service->discounts()->running()->first();
                $price_calculation->setLocationService($location_service);
                $upsell_calculation->setLocationService($location_service);
                if ($job_service->variable_type == 'Options') {
                    $service['option_prices'] = ['option' => json_decode($job_service->option),
                        'price' => $price_calculation->setOption(json_decode($job_service->option))->getUnitPrice(),
                        'upsell_price' => $upsell_calculation->setOption(json_decode($job_service->option))->getAllUpsellWithMinMaxQuantity()
                    ];
                } else {
                    $service['fixed_price'] = $price_calculation->getUnitPrice();
                    $service['fixed_upsell_price'] = $upsell_calculation->getAllUpsellWithMinMaxQuantity();
                }
                $service['discount'] = $discount ? [
                    'value' => (double)$discount->amount,
                    'is_percentage' => $discount->isPercentage(),
                    'cap' => (double)$discount->cap
                ] : null;
                $service['id'] = $job_service->service->id;
                $service['option'] = json_decode($job_service->option);
                $service['quantity'] = $job_service->quantity < $job_service->service->min_quantity ? $job_service->service->min_quantity : $job_service->quantity;
                $service['type'] = $job_service->variable_type;
                array_push($all_services, $service);
            }
            if (empty($all_services)) continue;
            $data['services'] = $all_services;
            array_push($final, $data);
        }
        return api_response($request, $final, 200, ['data' => $final]);
    }
}