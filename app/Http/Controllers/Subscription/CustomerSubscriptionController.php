<?php namespace App\Http\Controllers\Subscription;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\CustomerDeliveryAddress;
use App\Models\Location;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Dal\ServiceSubscription\ServiceSubscription;
use Sheba\Dal\ServiceSubscriptionDiscount\ServiceSubscriptionDiscount;
use App\Models\SubscriptionOrder;
use App\Transformers\PreferredTimeTransformer;
use App\Transformers\ServiceV2DeliveryChargeTransformer;
use App\Transformers\ServiceV2MinimalTransformer;
use App\Transformers\ServiceV2Transformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Checkout\SubscriptionOrderPlace\CustomerSubscriptionOrderPlaceFactory;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Jobs\PreferredTime;
use Sheba\LocationService\PriceCalculation;
use Sheba\Payment\Adapters\Payable\SubscriptionOrderAdapter;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Services\Type;
use Sheba\Subscription\ApproximatePriceCalculator;
use Throwable;

class CustomerSubscriptionController extends Controller
{
    public function getPartners(Request $request, PartnerListRequest $partnerListRequest)
    {
        try {
            $this->validate($request, [
                'date' => 'required|string',
                'time' => 'sometimes|required|string',
                'services' => 'required|string',
                'partner' => 'sometimes|required',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'subscription_type' => 'required|string',
                'filter' => 'string|in:sheba',
            ]);
            $partner = $request->has('partner') ? $request->partner : null;
            $request->merge(['date' => json_decode($request->date)]);
            $partnerListRequest->setRequest($request)->prepareObject();
            if (!$partnerListRequest->isValid()) {
                return api_response($request, null, 400, ['message' => 'Wrong Day for subscription']);
            }
            $partner_list = new SubscriptionPartnerList();
            $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
            $partner_list->filterPartnerByAvailability();
            $partner_list->removeShebaHelpDesk();
            $partners = $partner_list->partners;
            if ($request->has('show_reason')) return api_response($request, null, 200, ['reason' => $partner_list->getNotShowingReason()]);
            if ($partners->count() > 0) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                if ($request->has('filter') && $request->filter == 'sheba') {
                    $partner_list->sortByShebaPartnerPriority();
                } else {
                    $partner_list->sortByShebaSelectedCriteria();
                }
                $partner_list->removeKeysFromPartner();
                $partners = $partner_list->partners;
                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        } catch (HyperLocationNotFoundException $e) {
            logError($e);
            return api_response($request, null, 400, ['message' => 'Your are out of service area.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function placeSubscriptionRequest(Request $request, CustomerSubscriptionOrderPlaceFactory $factory)
    {
        try {
            $this->validate($request, [
                'date' => 'required|string',
                'time' => 'sometimes|required|string',
                'services' => 'required|string',
                'partner' => 'numeric',
                'address_id' => 'required|numeric',
                'subscription_type' => 'required|string',
                'sales_channel' => 'required|string',
            ]);
            $subscription_order = $factory->get($request)->place();
            return api_response($request, $subscription_order, 200, ['order' => [
                'id' => $subscription_order->id
            ]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $customer
     * @param $subscription
     * @param PaymentManager $payment_manager
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function clearPayment(Request $request, $customer, $subscription, PaymentManager $payment_manager)
    {
        $this->validate($request, [
            'payment_method' => 'required|string|in:bkash,wallet,cbl,online',
        ]);
        $customer = $request->customer;
        $payment_method = $request->payment_method;
        /** @var SubscriptionOrder $subscription_order */
        $subscription_order = SubscriptionOrder::find((int)$subscription);
        $subscription_order->calculate();
        if ($payment_method == 'wallet' && $subscription_order->due > $customer->shebaCredit()) {
            return api_response($request, null, 403, ['message' => 'You don\'t have sufficient credit.']);
        }
        $order_adapter = new SubscriptionOrderAdapter();
        $payable = $order_adapter->setModelForPayable($subscription_order)->setUser($customer)->getPayable();
        $payment = $payment_manager->setMethodName($payment_method)->setPayable($payable)->init();
        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
    }

    public function index(Request $request, $customer)
    {
        try {
            $customer = $request->customer;
            $subscription_orders_list = collect([]);
            list($offset, $limit) = calculatePagination($request);
            $subscription_orders = SubscriptionOrder::with(['orders.partnerOrders.jobs.jobServices', 'customer.profile'])
                ->where('customer_id', (int)$customer->id)->orderBy('created_at', 'desc');
            $subscription_order_count = $subscription_orders->count();
            $subscription_orders->skip($offset)->limit($limit);

            if ($request->has('status') && $request->status != 'all') {
                $subscription_orders = $subscription_orders->status($request->status);
            }

            foreach ($subscription_orders->get() as $subscription_order) {
                if (!$subscription_order->isPaid()) continue;
                $partner_orders = $subscription_order->orders->map(function ($order) {
                    return $order->lastPartnerOrder();
                });
                $format_partner_orders = $partner_orders->map(function ($partner_order) {
                    $last_job = $partner_order->order->lastJob();
                    return [
                        'id' => $partner_order->order->code(),
                        'job_id' => $last_job->id,
                        'schedule_date' => Carbon::parse($last_job->schedule_date),
                        'preferred_time' => Carbon::parse($last_job->schedule_date)->format('M-j') . ', ' . Carbon::parse($last_job->preferred_time_start)->format('h:ia'),
                        'is_completed' => $partner_order->closed_and_paid_at ? $partner_order->closed_and_paid_at->format('M-j, h:ia') : null,
                        'cancelled_at' => $partner_order->cancelled_at ? Carbon::parse($partner_order->cancelled_at)->format('M-j, h:i a') : null
                    ];
                });
                $served_orders = $format_partner_orders->filter(function ($partner_order) {
                    return $partner_order['is_completed'] != null;
                });
                $service_details = json_decode($subscription_order->service_details);
                $service_details_breakdown = $service_details->breakdown['0'];
                $service = Service::find((int)$service_details_breakdown->id);
                $schedules = collect(json_decode($subscription_order->schedules));

                $orders_list = [
                    'subscription_id' => $subscription_order->id,
                    "subscription_code" => $subscription_order->code(),
                    "service_name" => $service->name,
                    "app_thumb" => $service->app_thumb,
                    "billing_cycle" => $subscription_order->billing_cycle,
                    'customer_name' => $subscription_order->customer->profile->name,
                    'customer_mobile' => $subscription_order->customer->profile->mobile,
                    'address' => $subscription_order->deliveryAddress ? $subscription_order->deliveryAddress->address : "",
                    'location_name' => $subscription_order->location ? $subscription_order->location->name : "",
                    'ordered_for' => $subscription_order->deliveryAddress ? $subscription_order->deliveryAddress->name : "",
                    'is_paid' => $subscription_order->isPaid(),
                    "total_orders" => $served_orders->count(),
                    "preferred_time" => $schedules->first()->time,
                    "subscription_period" => Carbon::parse($subscription_order->billing_cycle_start)->format('M j') . ' - ' . Carbon::parse($subscription_order->billing_cycle_end)->format('M j'),
                    "completed_orders" => $served_orders->count() . '/' . $subscription_order->orders->count(),
                    'status' => $subscription_order->status,
                    "is_active" => Carbon::parse($subscription_order->billing_cycle_end) >= Carbon::today() ? 1 : 0,
                ];

                if ($partner = $subscription_order->partner) {
                    $orders_list["partner"] = [
                        "id" => $partner->id,
                        "name" => $partner->name,
                        "mobile" => $partner->mobile,
                        "logo" => $partner->logo
                    ];
                }
                $subscription_orders_list->push($orders_list);
            }

            if (count($subscription_orders_list) > 0) {
                return api_response($request, $subscription_orders_list, 200, [
                    'subscription_orders_list' => $subscription_orders_list,
                    'subscription_order_count' => $subscription_order_count
                ]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $customer, $subscription,
                         ApproximatePriceCalculator $approximatePriceCalculator,
                         PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                         JobDiscountHandler $job_discount_handler, ServiceV2MinimalTransformer $service_transformer)
    {
        try {
            $customer = $request->customer;
            $subscription_order = SubscriptionOrder::find((int)$subscription);
            $subscription_order->calculate(1);
            $partner = $subscription_order->partner;
            $partner_orders = $subscription_order->orders->map(function ($order) {
                return $order->lastPartnerOrder();
            });
            $format_partner_orders = $partner_orders->map(function ($partner_order) {
                $last_job = $partner_order->order->lastJob();
                return [
                    'id' => $partner_order->order->code(),
                    'job_id' => $last_job->id,
                    'job_status' => $last_job->status,
                    'partner_order_id' => $partner_order->id,
                    'schedule_date' => Carbon::parse($last_job->schedule_date),
                    'preferred_time' => Carbon::parse($last_job->schedule_date)->format('M-j') . ', ' . Carbon::parse($last_job->preferred_time_start)->format('h:ia'),
                    'is_completed' => $partner_order->closed_and_paid_at ? $partner_order->closed_and_paid_at->format('M-j, h:ia') : null,
                    'cancelled_at' => $partner_order->cancelled_at ? Carbon::parse($partner_order->cancelled_at)->format('M-j, h:i a') : null
                ];
            });
            $schedule_dates = $partner_orders->map(function ($partner_order) {
                $last_job = $partner_order->order->lastJob();
                $day_name = Carbon::parse($last_job->schedule_date)->format('l');
                return Carbon::parse(new Carbon('next ' . lcfirst($day_name)))->toDateString();
            })->toArray();
            usort($schedule_dates, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            });
            $next_order = [];
            foreach ($format_partner_orders->toArray() as $partner_order) {
                if (empty($partner_order['is_completed'])) {
                    $next_order = getDayName($partner_order['schedule_date']);
                    break;
                }
            }
            $served_orders = $format_partner_orders->filter(function ($partner_order) {
                return $partner_order['is_completed'] != null;
            });
            $service_details = json_decode($subscription_order->service_details);

            $service_details_breakdown = $service_details->breakdown['0'];
            $service = Service::find((int)$service_details_breakdown->id);

            /** @var ServiceSubscription $service_subscription */
            $service_subscription = $service->subscription;

            $schedules = collect(json_decode($subscription_order->schedules));
            $delivery_address = $subscription_order->deliveryAddress()->withTrashed()->first();

            /** @var Manager $manager */
            $manager = new Manager();
            $manager->setSerializer(new ArraySerializer());
            $location_service = LocationService::where('location_id', $subscription_order->location_id)->where('service_id', $service->id)->first();

            $variables = collect();
            $service_group = collect();
            $total_quantity = 0;
            $total_price = 0;
            foreach ($service_details->breakdown as $breakdown) {
                $selected_service = [
                    "option" => $breakdown->option,
                    "variable_type" => $service->variable_type
                ];
                if ($location_service) $service_transformer->setLocationService($location_service);
                $resource = new Item($selected_service, $service_transformer);
                $price_discount_data = $manager->createData($resource)->toArray();

                $data = [
                    'quantity' => $breakdown->quantity,
                    'questions' => empty($breakdown->questions) ? null : $breakdown->questions,
                    'options' => $breakdown->option,
                    'unit_price' => $price_discount_data['unit_price']
                ];
                $variables->push($data);
                $service_data = [
                    'variables' => empty($breakdown->questions) ? null : $breakdown->questions,
                    'unit' => $breakdown->unit,
                    'quantity' => $breakdown->quantity,
                    'price' => (double)$price_discount_data['unit_price'] * (double)$breakdown->quantity
                ];
                $total_quantity += $breakdown->quantity;
                $total_price += (double)$price_discount_data['unit_price'] * (double)$breakdown->quantity;
                $service_group->push($service_data);
            }

            $services = [[
                'id' => $service->id,
                'name' => $service->name,
                'service_group' => $service->variable_type == Type::OPTIONS ? $service_group : [],
                'unit' => $service->unit,
                'quantity' => $total_quantity,
                'price' => $total_price
            ]];

            $time = new PreferredTime($schedules->first()->time);

            $subscription_order_details = [
                "id" => $subscription_order->id,
                "service_subscription_id" => $service_subscription->id,
                "subscription_code" => $subscription_order->code(),
                "category_id" => $service->category->id,
                "category_name" => $service->category->name,
                'service_id' => $service->id,
                "service_name" => $service->name,
                "app_thumb" => $service->app_thumb,
                'description' => $service->description,
                "variables" => $variables,
                "service_list" => $services,
                "total_quantity" => $service_details->total_quantity,
                'quantity' => (double)$service_details_breakdown->quantity,
                'is_weekly' => $service_subscription->is_weekly,
                'is_monthly' => $service_subscription->is_monthly,
                'is_yearly' => $service_subscription->is_yearly,
                'min_weekly_qty' => $service_subscription->min_weekly_qty,
                'min_monthly_qty' => $service_subscription->min_monthly_qty,
                'min_yearly_qty' => $service_subscription->min_yearly_qty,
                "partner_id" => $subscription_order->partner_id,
                "partner_name" => $partner ? $partner->name : null,
                "logo" => $partner ? $partner->logo : null,
                "contact_person" => $partner ? $partner->getContactPerson() : null,
                "partner_slug" => $partner ? $partner->sub_domain : null,
                "partner_mobile" => $partner ? $partner->getContactNumber() : null,
                "partner_address" => $partner ? $partner->address : null,
                "avg_rating" => $partner ? (double)$partner->reviews()->avg('rating') : 0.00,
                "total_rating" => $partner ? $partner->reviews->count() : null,
                'customer_name' => $subscription_order->customer->profile->name,
                'customer_mobile' => $subscription_order->customer->profile->mobile,
                'address_id' => $delivery_address->id,
                'address' => $delivery_address->address,
                'location_name' => $subscription_order->location ? $subscription_order->location->name : "",
                'ordered_for' => $delivery_address->name,
                "billing_cycle" => $subscription_order->billing_cycle,
                "total_orders" => $subscription_order->orders->count(),
                "completed_orders" => $served_orders->count(),
                "orders_left" => $subscription_order->orders->count() - $served_orders->count(),
                "preferred_time" => $time->toReadableString(),
                "preferred_time_structured" => (new PreferredTimeTransformer())->transform($time),
                "next_order" => empty($next_order) ? null : $next_order,
                "days_left" => Carbon::today()->diffInDays(Carbon::parse($subscription_order->billing_cycle_end)),
                'original_price' => $service_details->original_price,
                'discount' => $service_details->discount,
                'total_price' => $subscription_order->orders->count() > 0 ? $subscription_order->totalPrice : $service_details->discounted_price,
                'subscription_delivery_charge' => $service_details->delivery_charge,
                'subscription_discounted_delivery_charge' => $service_details->discounted_delivery_charge,
                'subscription_delivery_discount_amount' => $service_details->delivery_charge - $service_details->discounted_delivery_charge,
                "paid_on" => $subscription_order->isPaid() ? $subscription_order->paid_at->format('M-j, Y') : null,
                'is_paid' => $subscription_order->isPaid(),
                "orders" => $format_partner_orders,
                'schedule_dates' => $schedule_dates,
                'paid' => $subscription_order->paid,
                'due' => $subscription_order->due,
                "subscription_additional_info" => $subscription_order->additional_info,
                "subscription_status" => $subscription_order->status,
                "subscription_period" => Carbon::parse($subscription_order->billing_cycle_start)->format('M j') . ' - ' . Carbon::parse($subscription_order->billing_cycle_end)->format('M j'),
                "subscription_start" => Carbon::parse($subscription_order->billing_cycle_start)->format('l, j F Y'),
                "subscription_end" => Carbon::parse($subscription_order->billing_cycle_end)->format('l, j F Y'),
            ];

            /** @var $discount ServiceSubscriptionDiscount $weekly_discount */
            $weekly_discount = $service_subscription->discounts()->where('subscription_type', 'weekly')->valid()->first();
            /** @var $discount ServiceSubscriptionDiscount $monthly_discount */
            $monthly_discount = $service_subscription->discounts()->where('subscription_type', 'monthly')->valid()->first();
            /** @var $discount ServiceSubscriptionDiscount $yearly_discount */
            $yearly_discount = $service_subscription->discounts()->where('subscription_type', 'yearly')->valid()->first();

            $subscription_order_details['weekly_discount'] = $weekly_discount ? [
                'value' => (double)$weekly_discount->discount_amount,
                'is_percentage' => $weekly_discount->isPercentage(),
                'cap' => (double)$weekly_discount->cap,
                'min_discount_quantity' => $weekly_discount->min_discount_qty
            ] : null;
            $subscription_order_details['monthly_discount'] = $monthly_discount ? [
                'value' => (double)$monthly_discount->discount_amount,
                'is_percentage' => $monthly_discount->isPercentage(),
                'cap' => (double)$monthly_discount->cap,
                'min_discount_quantity' => $monthly_discount->min_discount_qty
            ] : null;

            $subscription_order_details['yearly_discount'] = $yearly_discount ? [
                'value' => (double)$yearly_discount->discount_amount,
                'is_percentage' => $yearly_discount->isPercentage(),
                'cap' => (double)$yearly_discount->cap,
                'min_discount_quantity' => $yearly_discount->min_discount_qty
            ] : null;

            $resource = new Item($service->category,
                new ServiceV2DeliveryChargeTransformer($delivery_charge, $job_discount_handler, $subscription_order->location)
            );
            $delivery_charge_discount_data = $manager->createData($resource)->toArray();
            $subscription_order_details += $delivery_charge_discount_data;

            return api_response($request, $subscription_order_details, 200, ['subscription_order_details' => $subscription_order_details]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function checkRenewalStatus(Request $request, $customer, $subscription)
    {
        try {
            $customer = $request->customer;
            $subscription_order = SubscriptionOrder::find((int)$subscription);
            $partner_orders = $subscription_order->orders->map(function ($order) {
                return $order->lastPartnerOrder();
            });
            $delivery_address = CustomerDeliveryAddress::withTrashed()->find($partner_orders[0]->order->delivery_address_id);
            $geo = json_decode($delivery_address->geo_informations);

            $format_partner_orders = $partner_orders->map(function ($partner_order) {
                $last_job = $partner_order->order->lastJob();
                return [
                    'id' => $partner_order->order->code(),
                    'job_id' => $last_job->id,
                    'schedule_date' => Carbon::parse($last_job->schedule_date),
                    'preferred_time' => Carbon::parse($last_job->schedule_date)->format('M-j') . ', ' . Carbon::parse($last_job->preferred_time_start)->format('h:ia'),
                    'is_completed' => $partner_order->closed_and_paid_at ? $partner_order->closed_and_paid_at->format('M-j, h:ia') : null,
                    'cancelled_at' => $partner_order->cancelled_at ? Carbon::parse($partner_order->cancelled_at)->format('M-j, h:i a') : null
                ];
            });

            $schedule_dates = $partner_orders->map(function ($partner_order) {
                $last_job = $partner_order->order->lastJob();
                $day_name = Carbon::parse($last_job->schedule_date)->format('l');
                return Carbon::parse(new Carbon('next ' . lcfirst($day_name)))->toDateString();
            })->toArray();

            usort($schedule_dates, function ($a, $b) {
                return strtotime($a) - strtotime($b);
            });
            $schedules = collect(json_decode($subscription_order->schedules));

            $service_details = json_decode($subscription_order->service_details);
            $variables = collect();
            foreach ($service_details->breakdown as $breakdown) {
                $data = [
                    'id' => $breakdown->id,
                    'quantity' => $breakdown->quantity,
                    'option' => $breakdown->option
                ];

                $variables->push($data);
            }

            $request['date'] = $schedule_dates;
            $request['time'] = $schedules->first()->time;
            $request['services'] = json_encode($variables, true);
            $request['lat'] = (double)$geo->lat;
            $request['lng'] = (double)$geo->lng;
            $request['subscription_type'] = $subscription_order->billing_cycle;
            $partners = $this->findPartnersForSubscription($request, $partner_orders[0]->partner_id);
            if (count($partners) > 0) {
                return api_response($request, $partners, 200, ['status' => 'partner_available_on_time']);
            } else {
                $partners = $this->findPartnersForSubscription($request);
                if (count($partners) > 0)
                    return api_response($request, $partners, 200, ['status' => 'other_partners_available_on_time']);
                else
                    return api_response($request, $partners, 200, ['status' => 'no_partners_available_on_time']);
            }
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function serviceQuestionSet($service)
    {
        $questions = null;
        if ($service->variable_type == 'Options') {
            $questions = json_decode($service->variables)->options;
            foreach ($questions as &$question) {
                $question = collect($question);
                $question->put('input_type', $this->resolveInputTypeField($question->get('answers')));
                $question->put('screen', count($questions) > 3 ? 'slide' : 'normal');
                $explode_answers = explode(',', $question->get('answers'));
                $question->put('answers', $explode_answers);
            }
            if (count($questions) == 1) {
                $questions[0]->put('input_type', 'selectbox');
            }
        }
        return $questions;
    }

    private function breakdown_service_with_min_max_price($arrays, $min_price, $max_price, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }

        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->breakdown_service_with_min_max_price($arrays, $min_price, $max_price, $i + 1);

        $result = [];

        foreach ($arrays[$i] as $array_index => $v) {

            foreach ($tmp as $index => $t) {
                $result[] = is_array($t) ?
                    array(
                        'name' => $v . " - " . $t['name'],
                        'indexes' => array_merge(array($array_index), $t['indexes']),
                        'min_price' => $t['min_price'],
                        'max_price' => $t['max_price'],
                    ) :
                    array(
                        'name' => $v . " - " . $t,
                        'indexes' => array($array_index, $index),
                        'min_price' => $min_price,
                        'max_price' => $max_price
                    );
            }
        }

        return $result;
    }

    private function resolveInputTypeField($answers)
    {
        $answers = explode(',', $answers);
        return count($answers) <= 4 ? "radiobox" : "dropdown";
    }

    private function findPartnersForSubscription($request, $partner = null)
    {
        $partnerListRequest = new PartnerListRequest();
        $partnerListRequest->setRequest($request)->prepareObject();
        if (!$partnerListRequest->isValid()) {
            return api_response($request, null, 400, ['message' => 'Wrong Day for subscription']);
        }
        $partner_list = new SubscriptionPartnerList();
        $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
        $partners = $partner_list->partners->filter(function ($partner) {
            return $partner->is_available == 1 || $partner->id == config('sheba.sheba_help_desk_id');
        });
    }
}
