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
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Sheba\Business\MemberManager;
use Sheba\Checkout\Adapters\SubscriptionOrderAdapter;
use Sheba\Checkout\SubscriptionOrderPlace\B2bSubscriptionOrderPlaceFactory;
use Sheba\Checkout\PromotionCalculation;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Location\Coords;
use Sheba\Logs\Customer\JobLogs;
use Sheba\Map\Address;
use Sheba\Map\GeoCode;
use Sheba\Map\MapClientNoResultException;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Throwable;

class OrderController extends Controller
{
    use ModificationFields;

    /** @var MemberManager $memberManager */
    private $memberManager;

    /**
     * OrderController constructor.
     * @param MemberManager $member_manager
     */
    public function __construct(MemberManager $member_manager)
    {
        $this->memberManager = $member_manager;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $customer = $request->manager_member->customer;
        if (!$customer)
            return api_response($request, null, 200, ['orders' => [], 'total_orders' => 0]);

        list($offset, $limit) = calculatePagination($request);
        $customer = $customer->load([
            'orders' => function ($q) {
                $q->whereNotNull('business_id')->select('id', 'customer_id', 'partner_id', 'location_id', 'sales_channel', 'delivery_name', 'delivery_mobile', 'delivery_address', 'subscription_order_id', 'sales_channel')->orderBy('id', 'desc');
                $q->with([
                    'partnerOrders' => function ($q) {
                        $q->with([
                            'partner' => function ($q) {
                                $q->select('id', 'name', 'mobile', 'logo')->with([
                                    'resources' => function ($q) {
                                        $q->select('resources.id', 'profile_id')->with([
                                            'profile' => function ($q) {
                                                $q->select('id', 'name', 'pro_pic', 'mobile', 'email');
                                            }
                                        ]);
                                    }
                                ]);
                            }, 'order' => function ($q) {
                                $q->select('id', 'sales_channel', 'subscription_order_id');
                            }, 'jobs' => function ($q) {
                                $q->with([
                                    'resource' => function ($q) {
                                        $q->select('id', 'profile_id')->with([
                                            'profile' => function ($q) {
                                                $q->select('id', 'name', 'pro_pic', 'mobile', 'email');
                                            }
                                        ]);
                                    }, 'category' => function ($q) {
                                        $q->select('id', 'name', 'thumb', 'banner');
                                    }, 'review' => function ($q) {
                                        $q->select('id', 'rating', 'job_id');
                                    }, 'usedMaterials' => function ($q) {
                                        $q->select('id', 'job_id', 'material_name', 'material_price');
                                    }, 'jobServices'
                                ]);
                            }
                        ]);
                    }
                ]);
            }
        ]);
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

        if ($request->has('status') && $request->status != 'all') {
            $all_jobs = $all_jobs->where('status', $request->status)->values();
        }
        $start_date = $request->has('start_date') ? $request->start_date : null;
        $end_date = $request->has('end_date') ? $request->end_date : null;
        if ($start_date && $end_date) {
            $all_jobs = $all_jobs->filter(function ($job) use ($start_date, $end_date) {
                return ($job['created_at_date_time'] <= $start_date . ' 00:00:00') && ($job['created_at_date_time'] <= $end_date . ' 23:59:59');
            });
        }

        if ($request->has('search')) $all_jobs = $this->searchByTitle($all_jobs, $request)->values();
        if ($request->has('sort_by_id')) $all_jobs = $this->sortById($all_jobs, $request->sort_by_id)->values();
        if ($request->has('sort_by_title')) $all_jobs = $this->sortByTitle($all_jobs, $request->sort_by_title)->values();
        if ($request->has('sort_by_partner_name')) $all_jobs = $this->sortByPartnerName($all_jobs, $request->sort_by_partner_name)->values();
        if ($request->has('sort_by_status')) $all_jobs = $this->sortByStatus($all_jobs, $request->sort_by_status)->values();

        $total_jobs = count($all_jobs);
        if ($request->has('limit')) $all_jobs = collect($all_jobs)->splice($offset, $limit);

        return api_response($request, null, 200, ['orders' => $all_jobs, 'total_orders' => $total_jobs]);
    }

    /**
     * @param $orders
     * @return Collection
     */
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
            $partnerOrder->calculate(true);
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

    /**
     * @param Job $job
     * @param PartnerOrder $partnerOrder
     * @return Collection
     */
    private function getJobInformation(Job $job, PartnerOrder $partnerOrder)
    {
        $category = $job->category;
        return collect([
            'id' => $partnerOrder->id,
            'job_id' => $job->id,
            'order_code' => $partnerOrder->order->code(),
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'cancelled_date' => $partnerOrder->cancelled_at,
            'served_date' => $job->delivered_date ? $job->delivered_date->format('Y-m-d H:i:s') : null,
            'status' => $job->status,
            'can_give_review' => $this->canGiveReview($job),
            'partner_name' => $partnerOrder->partner ? $partnerOrder->partner->name : null,
            'partner_logo' => $partnerOrder->partner ? $partnerOrder->partner->logo : null,
            'rating' => $job->review != null ? $job->review->rating : null,
            'price' => $partnerOrder->getCustomerPayable(),
            'created_at' => $partnerOrder->created_at->format('Y-m-d'),
            'created_at_date_time' => $partnerOrder->created_at->toDateTimeString(),
            'version' => $partnerOrder->getVersion(),
            'original_price' => (double)$partnerOrder->jobPrices + $job->logistic_charge,
            'discount' => (double)$partnerOrder->totalDiscount,
            'discounted_price' => (double)$partnerOrder->totalPrice + $job->logistic_charge
        ]);
    }

    /**
     * @param $job
     * @return bool
     */
    private function canGiveReview($job)
    {
        $review = $job->review;
        if (!is_null($review) && $review->rating > 0) {
            return false;
        } else if (!!($job->partnerOrder->closed_and_paid_at)) {
            return true;
        }
        return false;
    }

    /**
     * @param $order
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function show($order, Request $request)
    {
        $customer = $request->manager_member->profile->customer;
        $partner_order = $request->partner_order;
        if (!$customer) return api_response($request, null, 404);

        $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders/$partner_order->id?remember_token=$customer->remember_token";

        $client = new Client();
        $res = $client->request('GET', $url);
        $response = json_decode($res->getBody());
        if ($response->code != 200) api_response($request, $response, $response->code);

        $order = $response->orders;
        $job = Job::find($order->jobs[0]->job_id);
        $question = null;
        $answer = [];
        $answer_text = null;
        $review_question_answer = null;
        if ($job->review && !$job->review->rates->isEmpty()) {
            $job->review->rates->each(function ($rate) use (&$question, &$answer, &$answer_text) {
                if (!is_null($rate->rate_answer_id)) $question = $rate->rate_question_id;
                if (!is_null($rate->rate_answer_id)) $answer[] = $rate->rate_answer_id;
                if ($rate->rate_answer_text) $answer_text = $rate->rate_answer_text;
            });
            $review_question_answer = ['question' => $question, 'answer' => $answer];
        }
        return api_response($request, $response, 200, [
            'order' => $order,
            'review_question_answer' => $review_question_answer,
            'answer_text' => $answer_text,
            'can_give_review' => $this->canGiveReview($job)
        ]);
    }

    /**
     * @param $order
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function getBills($order, Request $request)
    {
        $customer = $request->manager_member->profile->customer;
        $job = $request->job;
        if (!$customer) return api_response($request, null, 404);

        $url = config('sheba.api_url') . "/v2/customers/$customer->id/jobs/$job->id/bills?remember_token=$customer->remember_token";
        $client = new Client();
        $res = $client->request('GET', $url);
        $response = json_decode($res->getBody());
        if ($response->code != 200) return api_response($request, $response, $response->code);

        return api_response($request, $response, 200, ['order' => $response->bill]);
    }

    /**
     * @param $business
     * @param $order
     * @param Request $request
     * @param PaymentManager $payment_manager
     * @param OrderAdapter $order_adapter
     * @return JsonResponse
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

    /**
     * @param $partner_order_id
     * @return int
     */
    private function hasPreviousBkashTransaction($partner_order_id)
    {
        $time = Carbon::now()->subMinutes(1);
        $payment = Payment::whereHas('payable', function ($q) use ($partner_order_id) {
            $q->where([['type', 'partner_order'], ['type_id', $partner_order_id]]);
        })->where([['transaction_id', 'LIKE', '%bkash%'], ['created_at', '>=', $time]])->first();
        return $payment ? 1 : 0;
    }

    /**
     * @param Request $request
     * @param PartnerListRequest $partnerListRequest
     * @param PromotionCalculation $promotionCalculation
     * @return JsonResponse
     * @throws Exception
     */
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

        if (!$result['is_valid']) return api_response($request, null, 403, ['message' => 'Invalid Promo']);

        $voucher = $result['voucher'];
        $promo = array('amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title);
        return api_response($request, 1, 200, ['promotion' => $promo]);
    }

    /**
     * @param Request $request
     * @param GeoCode $geo_code
     * @param Address $address
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function placeOrder(Request $request, GeoCode $geo_code, Address $address)
    {
        $request->merge(['mobile' => trim(formatMobile($request->mobile))]);
        $this->validate($request, [
            'services' => 'required|string',
            'partner' => 'required',
            'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'time' => 'required|string',
            'delivery_address' => 'required|string',
            'issue_id' => 'sometimes|required|integer',
        ], ['mobile' => 'Invalid mobile number!']);

        $business = $request->business;
        $member = $request->manager_member;
        $customer = $member->profile->customer;
        $this->setModifier($customer);

        $address->setAddress($request->delivery_address);
        $geo = $geo_code->setAddress($address)->getGeo();

        if (!$customer) {
            $customer = $this->memberManager->createCustomerFromMember($member);
            $member = Member::find($member->id);
            $address = $this->memberManager->createAddress($member, $business, $request->delivery_address, $geo);
        } else {
            $coords = new Coords($geo->getLat(), $geo->getLng());
            $address = (new AddressValidator())->isAddressLocationExists($customer->delivery_addresses, $coords);
            if (!$address) $address = $this->memberManager->createAddress($member, $business, $request->delivery_address, $geo);
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

        if (!$order) return api_response($request, null, 422, ['message' => "You have selected a partner who doesn't provide service at you area. Please change your delivery address."]);

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
    }

    /**
     * @param Request $request
     * @param B2bSubscriptionOrderPlaceFactory $factory
     * @return JsonResponse
     */
    public function placeSubscriptionOrder(Request $request, B2bSubscriptionOrderPlaceFactory $factory)
    {
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
    }

    /**
     * @param $order
     * @return |null
     */
    private function sendNotifications($order)
    {
        try {
            (new NotificationRepository())->send($order);
        } catch (Throwable $e) {
            logError($e);
            return null;
        }
    }

    /**
     * @param $all_jobs
     * @param string $sort
     * @return mixed
     */
    private function sortById($all_jobs, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $all_jobs->$sort_by(function ($job) {
            return strtoupper($job['id']);
        });
    }

    /**
     * @param $all_jobs
     * @param string $sort
     * @return mixed
     */
    private function sortByTitle($all_jobs, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $all_jobs->$sort_by(function ($job) {
            return strtoupper($job['category_name']);
        });
    }

    /**
     * @param $all_jobs
     * @param string $sort
     * @return mixed
     */
    private function sortByPartnerName($all_jobs, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $all_jobs->$sort_by(function ($job) {
            return strtoupper($job['partner_name']);
        });
    }

    /**
     * @param $all_jobs
     * @param string $sort
     * @return mixed
     */
    private function sortByStatus($all_jobs, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $all_jobs->$sort_by(function ($job) {
            return strtoupper($job['status']);
        });
    }

    /**
     * @param $all_jobs
     * @param Request $request
     * @return Collection
     */
    private function searchByTitle($all_jobs, Request $request)
    {
        return $all_jobs->filter(function ($job) use ($request) {
            return str_contains(strtoupper($job['category_name']), strtoupper($request->search));
        });
    }
}
