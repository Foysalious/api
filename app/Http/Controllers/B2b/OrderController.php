<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\InspectionItemIssue;
use App\Models\Member;
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
            $customer = $request->manager_member->profile->customer;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders?remember_token=$customer->remember_token&for=business";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['orders' => $response->orders]) : api_response($request, $response, $response->code);
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
        try {
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
            $request->merge(['lat' => (double)$geo->lat, 'lng' => (double)$geo->lng]);
            $hyper_local = HyperLocal::insidePolygon((double)$geo->lat, (double)$geo->lng)->with('location')->first();
            $location = $hyper_local ? $hyper_local->location->id : null;
            $partnerListRequest->setRequest($request)->setLocation($location)->prepareObject();
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
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
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
            $request->merge(['customer' => $customer,
                'address_id' => $address->id,
                'name' => $business->name, 'payment_method' => 'cod', 'mobile' => $member->profile->mobile,
                'business_id' => $business->id, 'sales_channel' => $request->sales_channel?:constants('SALES_CHANNELS')['B2B']['name'], 'voucher' => $request->voucher]);
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
