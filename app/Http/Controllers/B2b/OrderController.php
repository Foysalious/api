<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\InspectionItemIssue;
use App\Models\Job;
use App\Models\Member;
use App\Models\PartnerOrder;
use App\Models\Payment;
use App\Repositories\NotificationRepository;
use App\Sheba\Address\AddressValidator;
use App\Sheba\Checkout\Checkout;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Business\MemberManager;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Checkout\SubscriptionOrderPlace\B2bSubscriptionOrderPlaceFactory;
use Sheba\Checkout\PromotionCalculation;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Location\Coords;
use Sheba\Logs\Customer\JobLogs;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;

class OrderController extends Controller
{
    use ModificationFields;

    private $memberManager;

    public function __construct(MemberManager $member_manager)
    {
        $this->memberManager = $member_manager;
    }

    public function index(Request $request)
    {
        try {
            $customer = $request->manager_member->customer;
            list($offset, $limit) = calculatePagination($request);
            $customer = $customer->load([
                'orders' => function ($q) {
                    $q->whereNotNull('business_id')->select('id', 'customer_id', 'partner_id', 'location_id', 'sales_channel', 'delivery_name', 'delivery_mobile', 'delivery_address', 'subscription_order_id', 'sales_channel')->orderBy('id', 'desc');
                    $q->with([
                        'partnerOrders' => function ($q) {
                            $q->select('id', 'order_id', 'partner_id','created_at')->with([
                                'partner' => function ($q) {
                                    $q->select('id', 'name', 'mobile')->with([
                                        'resources' => function ($q) {
                                            $q->select('resources.id', 'profile_id')->with([
                                                'profile' => function ($q) {
                                                    $q->select('id', 'name', 'pro_pic', 'mobile', 'email');
                                                }]);
                                        }]);
                                },
                                'order' => function ($q) {
                                    $q->select('id', 'sales_channel', 'subscription_order_id');
                                },
                                'jobs' => function ($q) {
                                    $q->select('id', 'partner_order_id', 'category_id', 'job_name', 'service_id', 'service_name', 'resource_id', 'schedule_date', 'preferred_time', 'preferred_time_start', 'preferred_time_end', 'status', 'delivered_date')->with([
                                        'resource' => function ($q) {
                                            $q->select('id', 'profile_id')->with([
                                                'profile' => function ($q) {
                                                    $q->select('id', 'name', 'pro_pic', 'mobile', 'email');
                                                }]);
                                        },
                                        'category' => function ($q) {
                                            $q->select('id', 'name', 'thumb', 'banner');
                                        },
                                        'review' => function ($q) {
                                            $q->select('id', 'rating', 'job_id');
                                        }]);
                                }]);
                        }]);
                }]);
            dd(count($customer->orders));
            if (count($customer->orders) > 0) {
                $all_jobs = $this->getInformation($customer->orders);
                $cancelled_served_jobs = $all_jobs->filter(function ($job) {
                    return $job['cancelled_date'] != null || $job['status'] == 'Served';
                });
                $others = $all_jobs->diff($cancelled_served_jobs);
                $all_jobs = $others->merge($cancelled_served_jobs);

            } else {
                $all_jobs = collect();
            }
            $total_jobs = count($all_jobs);
            if ($request->has('limit')) $all_jobs = collect($all_jobs)->splice($offset, $limit);

            return api_response($request, $all_jobs, 200, [
                'orders' => $all_jobs,
                'total_orders' => $total_jobs,
                ]);

            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders?remember_token=$customer->remember_token&for=business";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['orders' => $response]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            dd($e);
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function getInformation($orders)
    {
        $all_jobs = collect();
        foreach ($orders as $order) {
            $partnerOrders = $order->partnerOrders;
            $cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at != null;
            })->sortByDesc('cancelled_at');
            $not_cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at == null;
            });
            $partnerOrder = $not_cancelled_partnerOrders->count() == 0 ? $cancelled_partnerOrders->first() : $not_cancelled_partnerOrders->first();
            if (!$partnerOrder->cancelled_at) {
                $job = ($partnerOrder->jobs->filter(function ($job) {
                    return $job->status !== 'Cancelled';
                }))->first();
            } else {
                $job = $partnerOrder->jobs->first();
            }
            if ($job != null) $all_jobs->push($this->getJobInformation($job, $partnerOrder));
        }
        return $all_jobs;
    }

    private function getJobInformation(Job $job, PartnerOrder $partnerOrder)
    {
        $category = $job->category;
        $show_expert = $job->canCallExpert();

        return collect(array(
            'id' => $partnerOrder->id,
            'job_id' => $job->id,
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
            'served_date' => $job->delivered_date ? $job->delivered_date->format('Y-m-d H:i:s') : null,

            'cancelled_date' => $partnerOrder->cancelled_at,
            'schedule_date_readable' => (Carbon::parse($job->schedule_date))->format('M j, Y'),
            'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
            'status' => $job->status,

            'partner_name' => $partnerOrder->partner ? $partnerOrder->partner->name : null,
            'partner_logo' => $partnerOrder->partner ? $partnerOrder->partner->logo : null,

            'resource_name' => $job->resource ? $job->resource->profile->name : null,
            'resource_pic' => $job->resource ? $job->resource->profile->pro_pic : null,
            'contact_number' => $show_expert ? ($job->resource ? $job->resource->profile->mobile : null) : ($partnerOrder->partner ? $partnerOrder->partner->getManagerMobile() : null),

            'contact_person' => $show_expert ? 'expert' : 'partner',
            'rating' => $job->review != null ? $job->review->rating : null,
            'order_code' => $partnerOrder->order->code(),
            'created_at' => $partnerOrder->created_at->format('Y-m-d'),
            'created_at_timestamp' => $partnerOrder->created_at->timestamp,
        ));
    }

    public function show($order, Request $request)
    {
        try {
            $customer = $request->manager_member->profile->customer;
            $partner_order = $request->partner_order;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders/$partner_order->id?remember_token=$customer->remember_token";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['order' => $response->orders]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getBills($order, Request $request)
    {
        try {
            $customer = $request->manager_member->profile->customer;
            $job = $request->job;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/jobs/$job->id/bills?remember_token=$customer->remember_token";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['order' => $response->bill]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $business
     * @param $order
     * @param Request $request
     * @param PaymentManager $payment_manager
     * @param OrderAdapter $order_adapter
     * @return \Illuminate\Http\JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function clearBills($business, $order, Request $request, PaymentManager $payment_manager, OrderAdapter $order_adapter)
    {
        $this->validate($request, [
            'payment_method' => 'sometimes|required|in:' . implode(',', AvailableMethods::getRegularPayments()),
        ]);
        $payment_method = $request->has('payment_method') ? $request->payment_method : 'online';
        if ($payment_method == 'bkash' && $this->hasPreviousBkashTransaction($request->job->partner_order_id)) {
            return api_response($request, null, 500, ['message' => "Can't send multiple requests within 1 minute."]);
        }
        $payable = $order_adapter->setPartnerOrder($request->job->partnerOrder)->setPaymentMethod($payment_method)->getPayable();
        $payment = $payment_manager->setMethodName($payment_method)->setPayable($payable)->init();
        return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
    }

    private function hasPreviousBkashTransaction($partner_order_id)
    {
        $time = Carbon::now()->subMinutes(1);
        $payment = Payment::whereHas('payable', function ($q) use ($partner_order_id) {
            $q->where([['type', 'partner_order'], ['type_id', $partner_order_id]]);
        })->where([['transaction_id', 'LIKE', '%bkash%'], ['created_at', '>=', $time]])->first();
        return $payment ? 1 : 0;
    }

    public function applyPromo(Request $request, PartnerListRequest $partnerListRequest, PromotionCalculation $promotionCalculation)
    {
        $this->validate($request, [
            'services' => 'required|string',
            'partner' => 'required',
            'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'time' => 'required|string',
            'code' => 'required|string'
        ]);
        $business = $request->business;
        $member = $request->manager_member;
        $customer = $member->profile->customer;
        $geo = json_decode($business->geo_informations);
        if (!$customer) $customer = $this->memberManager->createCustomerFromMember($member);

        $hyper_local = HyperLocal::insidePolygon((double)$geo->lat, (double)$geo->lng)->with('location')->first();
        $location = $hyper_local ? $hyper_local->location->id : null;
        $request->merge(['lat' => (double)$geo->lat, 'lng' => (double)$geo->lng, 'location' => $location]);

        $partnerListRequest->setRequest($request)->setGeo($geo->lat, $geo->lng)->setLocation($location)->prepareObject();
        $order_amount = $promotionCalculation->calculateOrderAmount($partnerListRequest, $request->partner);
        if (!$order_amount) return api_response($request, null, 403);
        $result = voucher($request->code)
            ->check($partnerListRequest->selectedCategory->id, $request->partner, $location, $customer, $order_amount, constants('SALES_CHANNELS')['B2B']['name'])
            ->reveal();

        if ($result['is_valid']) {
            $voucher = $result['voucher'];
            $promo = array('amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title);
            return api_response($request, 1, 200, ['promotion' => $promo]);
        } else {
            return api_response($request, null, 403, ['message' => 'Invalid Promo']);
        }
    }

    public function placeOrder(Request $request)
    {
        try {
            $request->merge(['mobile' => trim(formatMobile($request->mobile))]);
            $this->validate($request, [
                'services' => 'required|string',
                'partner' => 'required',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'issue_id' => 'sometimes|required|integer',
            ], ['mobile' => 'Invalid mobile number!']);

            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            $this->setModifier($customer);
            if (!$customer) {
                $customer = $this->memberManager->createCustomerFromMember($member);
                $member = Member::find($member->id);
                $address = $this->memberManager->createAddress($member, $business);
            } else {
                $geo = json_decode($business->geo_informations);
                $coords = new Coords($geo->lat, $geo->lng);
                $address = (new AddressValidator())->isAddressLocationExists($customer->delivery_addresses, $coords);
                if (!$address) $address = $this->memberManager->createAddress($member, $business);
            }
            $order = new Checkout($customer);
            $request->merge([
                'customer' => $customer,
                'address_id' => $address->id,
                'name' => $request->has('delivery_name') ? $request->delivery_name : $business->name,
                'payment_method' => 'cod',
                'mobile' => $request->has('mobile') ? $request->mobile : $member->profile->mobile,
                'business_id' => $business->id,
                'sales_channel' => $request->sales_channel ?: constants('SALES_CHANNELS')['B2B']['name'],
                'voucher' => $request->voucher
            ]);

            $order = $order->placeOrder($request);
            if ($order) {
                if ($request->has('issue_id')) {
                    $issue = InspectionItemIssue::find((int)$request->issue_id);
                    $issue->update($this->withBothModificationFields(['order_id' => $order->id, 'status' => 'closed']));
                }
                $this->sendNotifications($order);
                return api_response($request, $order, 200, [
                    'job_id' => $order->jobs->first()->id,
                    'order_id' => $order->jobs->first()->partnerOrder->id,
                    'order_code' => $order->code()
                ]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function placeSubscriptionOrder(Request $request, B2bSubscriptionOrderPlaceFactory $factory)
    {
        try {
            $this->validate($request, [
                'date' => 'required|string',
                'time' => 'sometimes|required|string',
                'services' => 'required|string',
                'partner' => 'required|numeric',
                'subscription_type' => 'required|string',
                'additional_info' => 'string'
            ]);
            $this->setModifier($request->manager_member->profile->customer);
            $subscription_order = $factory->get($request)->place();
            $order = (new SubscriptionOrderAdapter($subscription_order))->convertToOrder();
            return api_response($request, $order, 200, ['order' => ['id' => $order->id]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function sendNotifications($order)
    {
        try {
            (new NotificationRepository())->send($order);
        } catch (\Throwable $e) {
            logError($e);
            return null;
        }
    }
}
