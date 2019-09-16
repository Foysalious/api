<?php namespace App\Http\Controllers\Subscription;

use App\Exceptions\HyperLocationNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\CustomerDeliveryAddress;
use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\SubscriptionOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Checkout\Requests\SubscriptionOrderRequest;
use Sheba\Checkout\SubscriptionOrderPlace;
use Sheba\Payment\Adapters\Payable\SubscriptionOrderAdapter;
use Sheba\Payment\ShebaPayment;
use Sheba\Subscription\ApproximatePriceCalculator;

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
            $partners = $partner_list->partners->filter(function ($partner) {
                return $partner->is_available == 1 || $partner->id == config('sheba.sheba_help_desk_id');
            });
            if ($partners->count() > 0) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                if ($request->has('filter') && $request->filter == 'sheba') {
                    $partner_list->sortByShebaPartnerPriority();
                } else {
                    $partner_list->sortByShebaSelectedCriteria();
                }
                $partners->each(function ($partner, $key) {
                    $partner['rating'] = round($partner->rating, 2);
                    array_forget($partner, 'wallet');
                    array_forget($partner, 'package_id');
                    array_forget($partner, 'geo_informations');
                    removeRelationsAndFields($partner);
                });

                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        } catch (HyperLocationNotFoundException $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 400, ['message' => 'Your are out of service area.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function placeSubscriptionRequest(Request $request, SubscriptionOrderRequest $subscriptionOrderRequest, SubscriptionOrderPlace $subscriptionOrder)
    {
        try {
            $this->validate($request, [
                'date' => 'required|string',
                'time' => 'sometimes|required|string',
                'services' => 'required|string',
                'partner' => 'required|numeric',
                'address_id' => 'required|numeric',
                'subscription_type' => 'required|string',
                'sales_channel' => 'required|string',
            ]);
            $address = CustomerDeliveryAddress::withTrashed()->where('id', $request->address_id)->first();
            $subscriptionOrderRequest->setRequest($request)->setSalesChannel($request->sales_channel)->setCustomer($request->customer)->setAddress($address)
                ->setDeliveryMobile($request->mobile)
                ->setDeliveryName($request->name)
                ->prepareObject();
            $subscriptionOrder = $subscriptionOrder->setSubscriptionRequest($subscriptionOrderRequest)->place();
            return api_response($request, $subscriptionOrder, 200, ['order' => [
                'id' => $subscriptionOrder->id
            ]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function clearPayment(Request $request, $customer, $subscription, ShebaPayment $sheba_payment)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'required|string|in:bkash,wallet,cbl,online',
            ]);
            $customer = $request->customer;
            $payment_method = $request->payment_method;
            /** @var SubscriptionOrder $subscription_order */
            $subscription_order = SubscriptionOrder::find((int)$subscription);
            if ($payment_method == 'wallet' && $subscription_order->getTotalPrice() > $customer->shebaCredit()) {
                return api_response($request, null, 403, ['message' => 'You don\'t have sufficient credit.']);
            }
            $order_adapter = new SubscriptionOrderAdapter();
            $payable = $order_adapter->setModelForPayable($subscription_order)->getPayable();
            $payment = $sheba_payment->setMethod($payment_method)->init($payable);
            return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function index(Request $request, $customer)
    {
        try {
            $customer = $request->customer;
            $subscription_orders_list = collect([]);
            $subscription_orders = SubscriptionOrder::where('customer_id', (int)$customer->id)->orderBy('created_at', 'desc')->get();

            foreach ($subscription_orders as $subscription_order) {

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


                #$schedules = collect(json_decode($subscription_order->schedules));
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
                    "is_active" => Carbon::parse($subscription_order->billing_cycle_end) >= Carbon::today() ? 1 : 0,
                    "partner" =>
                        [
                            "id" => $subscription_order->partner_id,
                            "name" => $service_details->name,
                            "mobile" => $subscription_order->partner->mobile,
                            "logo" => $service_details->logo
                        ]
                ];
                $subscription_orders_list->push($orders_list);
            }
            return api_response($request, $subscription_orders_list, 200, ['subscription_orders_list' => $subscription_orders_list]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show(Request $request, $customer, $subscription, ApproximatePriceCalculator $approximatePriceCalculator)
    {
        try {
            $customer = $request->customer;
            $subscription_order = SubscriptionOrder::find((int)$subscription);
            $partner = $subscription_order->partner;
            $partner_orders = $subscription_order->orders->map(function ($order) {
                return $order->lastPartnerOrder();
            });

            $format_partner_orders = $partner_orders->map(function ($partner_order) {
                $last_job = $partner_order->order->lastJob();
                return [
                    'id' => $partner_order->order->code(),
                    'job_id' => $last_job->id,
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

            $variables = collect();
            foreach ($service_details->breakdown as $breakdown) {
                if (empty($breakdown->questions)) {
                    $data = [
                        'quantity' => $breakdown->quantity,
                        'questions' => null,
                        'options' => $breakdown->option
                    ];
                } else {
                    $data = [
                        'quantity' => $breakdown->quantity,
                        'questions' => $breakdown->questions,
                        'options' => $breakdown->option
                    ];
                }
                $variables->push($data);
            }

            $service_details_breakdown = $service_details->breakdown['0'];

            $service = Service::find((int)$service_details_breakdown->id);
            $service_subscription = $service->subscription;
            $schedules = collect(json_decode($subscription_order->schedules));
            $subscription_order_details = [
                "subscription_code" => $subscription_order->code(),
                "category_id" => $service->category->id,
                "category_name" => $service->category->name,
                'service_id' => $service->id,
                "service_name" => $service->name,
                "app_thumb" => $service->app_thumb,
                "variables" => $variables,
                "total_quantity" => $service_details->total_quantity,
                'quantity' => (double)$service_details_breakdown->quantity,

                'is_weekly' => $service_subscription->is_weekly,
                'is_monthly' => $service_subscription->is_monthly,
                'min_weekly_qty' => $service_subscription->min_weekly_qty,
                'min_monthly_qty' => $service_subscription->min_monthly_qty,

                "partner_id" => $subscription_order->partner_id,
                "partner_name" => $service_details->name,
                "partner_slug" => $partner->sub_domain,
                "partner_mobile" => $partner->getContactNumber(),
                "logo" => $service_details->logo,
                "avg_rating" => (double)$partner->reviews()->avg('rating'),
                "total_rating" => $partner->reviews->count(),

                'customer_name' => $subscription_order->customer->profile->name,
                'customer_mobile' => $subscription_order->customer->profile->mobile,
                'address_id' => $subscription_order->deliveryAddress->id,
                'address' => $subscription_order->deliveryAddress->address,
                'location_name' => $subscription_order->location ? $subscription_order->location->name : "",
                'ordered_for' => $subscription_order->deliveryAddress->name,
                'subscription_status' => $subscription_order->status,


                "billing_cycle" => $subscription_order->billing_cycle,
                "subscription_period" => Carbon::parse($subscription_order->billing_cycle_start)->format('M j') . ' - ' . Carbon::parse($subscription_order->billing_cycle_end)->format('M j'),
                "total_orders" => $subscription_order->orders->count(),
                "completed_orders" => $served_orders->count(),

                "orders_left" => $subscription_order->orders->count() - $served_orders->count(),
                "preferred_time" => $schedules->first()->time,
                "next_order" => empty($next_order) ? null : $next_order,
                "days_left" => Carbon::today()->diffInDays(Carbon::parse($subscription_order->billing_cycle_end)),

                'original_price' => $service_details->original_price,
                'discount' => $service_details->discount,
                'total_price' => $service_details->discounted_price,
                "paid_on" => !empty($subscription_order->paid_at) ? Carbon::parse($subscription_order->paid_at)->format('M-j, Y') : null,
                'is_paid' => $subscription_order->isPaid(),
                "orders" => $format_partner_orders,
                'schedule_dates' => $schedule_dates
            ];

            return api_response($request, $subscription_order_details, 200, ['subscription_order_details' => $subscription_order_details]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            $delivery_address = CustomerDeliveryAddress::find($partner_orders[0]->order->delivery_address_id);
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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

        $result = array();

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