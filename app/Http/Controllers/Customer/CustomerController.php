<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Location;
use App\Models\Reward;
use App\Models\RewardAction;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Dal\LocationService\LocationService;
use App\Models\Review;
use Sheba\Dal\Service\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Dal\Discount\Discount;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\JobDiscount\JobDiscountCheckingParams;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\CorruptedPriceStructureException;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Service\ServiceQuestion;

class CustomerController extends Controller
{
    /**
     * @param $customer
     * @param Request $request
     * @param PriceCalculation $price_calculation
     * @param DeliveryCharge $delivery_charge
     * @param JobDiscountHandler $job_discount_handler
     * @param UpsellCalculation $upsell_calculation
     * @param ServiceQuestion $service_question
     * @return JsonResponse
     * @throws InvalidDiscountType
     * @throws CorruptedPriceStructureException
     */
    public function getOrderAgain($customer, Request $request, PriceCalculation $price_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation, ServiceQuestion $service_question): JsonResponse
    {
        $customer = $request->customer;
        $location = null;
        if ($request->has('lat')) {
            $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
            if (!is_null($hyper_location)) $location = $hyper_location->location_id;
        }
        if (!$location) return api_response($request, null, 404);
        $reviews = Review::where([['customer_id', $customer->id], ['rating', '>=', 4]])->select('id', 'category_id', 'job_id', 'rating', 'partner_id')->with([
            'category' => function ($q) {
                $q->select('id', 'name', 'thumb', 'app_thumb', 'banner', 'app_banner', 'frequency_in_days', 'publication_status', 'delivery_charge', 'min_order_amount', 'is_auto_sp_enabled', 'max_order_amount', 'is_vat_applicable');
            }, 'job' => function ($q) {
                $q->select('id', 'category_id', 'partner_order_id')->with('category')->with([
                    'jobServices' => function ($q) {
                        $q->select('id', 'job_id', 'service_id', 'quantity', 'option', 'variable_type', 'created_at')->with([
                            'service' => function ($q) {
                                $q->select('id', 'name', 'min_quantity', 'thumb', 'app_thumb', 'banner', 'app_banner', 'variables', 'variable_type', 'publication_status', 'is_inspection_service');
                            }
                        ]);
                    }, 'partnerOrder' => function ($q) {
                        $q->select('id', 'order_id', 'partner_id')->with([
                            'partner' => function ($q) {
                                $q->select('id', 'name', 'logo');
                            }, 'order' => function ($q) {
                                $q->select('id', 'location_id');
                            }
                        ]);
                    }
                ]);
            }
        ])->whereHas('category', function ($q) use ($location) {
            $q->published()->hasLocation($location)->select('id', 'publication_status');
        })->whereHas('job', function ($q) use ($location) {
            $q->whereHas('jobServices', function ($q) use ($location) {
                $q->whereHas('service', function ($q) use ($location) {
                    $q->published()->select('id', 'publication_status')->hasLocation($location);
                });
            });
        })->where('created_at', '>=', Carbon::now()->subMonths(6)->toDateTimeString())->orderBy('id', 'desc');

        if ($request->has('category_id')) {
            $reviews = $reviews->where('category_id', $request->category_id);
        }
        $reviews = $reviews->get();

        if (count($reviews) == 0) return api_response($request, null, 404);

        $final = collect();
        foreach ($reviews->groupBy('category_id') as $key => $reviews) {
            foreach ($reviews as $review) {
                if ($review->job->jobServices->count() == 0) continue;
                if ($this->canThisServiceAvailableForOrderAgain($final, $review->job)) continue;
                $data = [];
                $data['category'] = clone $review->category;
                $data['category']['delivery_charge'] = $delivery_charge->setCategory($review->category)->setLocation(Location::find($location))->get();
                $discount_checking_params = (new JobDiscountCheckingParams())->setDiscountableAmount($data['category']['delivery_charge']);
                $job_discount_handler->setType(DiscountTypes::DELIVERY)->setCategory($review->category)->setCheckingParams($discount_checking_params)->calculate();
                /** @var Discount $delivery_discount */
                $delivery_discount = $job_discount_handler->getDiscount();
                $data['category']['delivery_discount'] = $delivery_discount ? [
                    'value' => (double)$delivery_discount->amount, 'is_percentage' => $delivery_discount->is_percentage, 'cap' => (double)$delivery_discount->cap, 'min_order_amount' => (double)$delivery_discount->rules->getMinOrderAmount()
                ] : null;
                $all_services = [];
                foreach ($review->job->jobServices as $job_service) {
                    /** @var Service $service */
                    $service = clone $job_service->service;
                    /** @var array $option */
                    $option = json_decode($job_service->option);
                    /** @var LocationService $location_service */
                    $location_service = LocationService::where('location_id', $review->job->partnerOrder->order->location_id)->where('service_id', $job_service->service_id)->first();
                    if (!$location_service) continue;
                    /** @var ServiceDiscount $discount */
                    $discount = $location_service->discounts()->running()->first();
                    $price_calculation->setLocationService($location_service);
                    $upsell_calculation->setLocationService($location_service);
                    if ($service->isOptions()) {
                        if (count($option) == 0) continue;
                        $service['option_prices'] = [
                            'option' => $option, 'price' => $price_calculation->setOption($option)->getUnitPrice(), 'upsell_price' => $upsell_calculation->setOption($option)->getAllUpsellWithMinMaxQuantity()
                        ];
                        if (!$service['option_prices']['price']) continue;
                    } else {
                        $service['fixed_price'] = $price_calculation->getUnitPrice();
                        $service['fixed_upsell_price'] = $upsell_calculation->getAllUpsellWithMinMaxQuantity();
                    }
                    $service['discount'] = $discount ? [
                        'value' => (double)$discount->amount, 'is_percentage' => $discount->isPercentage(), 'cap' => (double)$discount->cap
                    ] : null;
                    $service['id'] = $job_service->service->id;
                    $service['option'] = $option;
                    if ($job_service->variable_type !== $job_service->service->variable_type) continue;
                    $service['question'] = count($option) > 0 ? $service_question->setService($job_service->service)->getQuestionForThisOption(json_decode($job_service->option)) : null;
                    $service['quantity'] = $job_service->quantity < $job_service->service->min_quantity ? $job_service->service->min_quantity : $job_service->quantity;
                    $service['type'] = $service->variable_type;
                    $service['is_inspection_service'] = $service->is_inspection_service;
                    array_forget($service, ['variables', 'variable_type']);
                    array_push($all_services, $service);
                }
                if (empty($all_services)) continue;
                $data['category']['is_inspection_service'] = $all_services[0]->is_inspection_service;
                $data['category']['services'] = $all_services;
                $data['category']['vat_percentage'] = config('sheba.category_vat_in_percentage');
                $data['category']['max_order_amount'] = $data['category']['max_order_amount'] ? (double) $data['category']['max_order_amount'] : null;
                $data['category']['app_thumb_sizes'] = getResizedUrls($data['category']['app_thumb'], 100, 100);
                $data['rating'] = $review->rating;
                $data['partner'] = $review->job->partnerOrder->partner;
                $final->push(collect($data));
            }
        }
        if (count($final) > 0) return api_response($request, $final, 200, ['data' => $final]);

        return api_response($request, null, 404);
    }

    /**
     * @param $final
     * @param Job $job
     * @return int
     */
    private function canThisServiceAvailableForOrderAgain($final, Job $job): int
    {
        if (count($final) == 0) return 0;
        $group_by_category = $final->groupBy('category.id');
        $same_category_orders = $group_by_category->get($job->category_id);
        if ($same_category_orders) {
            foreach ($same_category_orders as $review) {
                $review_services = collect($review['category']['services']);
                $job_services = $job->jobServices;
                $count = count($job_services);
                if (count($review_services) != $count) return 0;
                $same = 0;
                foreach ($job_services as $job_service) {
                    if ($job_service->variable_type == 'Fixed') {
                        if (!$job_service->service->isFixed()) return 1;
                        foreach ($review_services as $service) {
                            if ($service->id == $job_service->service_id) $same++;
                        }
                    } else {
                        if (!$job_service->service->isOptions()) return 1;
                        foreach ($review_services as $service) {
                            if ($service->id == $job_service->service_id) {
                            }
                        }
                    }
                }
                if ($same == $count) return 1;
            }
        }

        return 0;
    }

    /**
     * @param $customer
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfileCompletion($customer, Request $request): JsonResponse
    {
        $reward_action = RewardAction::where('event_name', 'profile_complete')->latest('id')->first();
        $data = [];
        $data['is_completed'] = $request->customer->is_completed;
        $data['reward_active'] = 0;
        if ($reward_action)
        {
            $reward = Reward::where('detail_id', $reward_action->id)->select('rewards.*')->get();
            $decision = count($reward) > 0 ? (($reward[0]->start_time <= Carbon::now()) && ($reward[0]->end_time >= Carbon::now())) : null;
            if ($decision && $data['is_completed'] == 0)  {
                $data['reward_active'] = 1;
                $data['not_complete_profile'] = "https://cdn-marketplacedev.s3.ap-south-1.amazonaws.com/sheba_xyz/images/png/sheba-credit-banner.png";
                $data['reward_amount'] = $reward[0]->amount;
            }
        }

        return api_response($request, $data, 200, ['data' => $data]);

    }
}
