<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Member;
use App\Models\Payment;
use App\Sheba\Address\AddressValidator;
use App\Sheba\Checkout\Checkout;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\PromotionCalculation;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Location\Coords;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;
use Sheba\Voucher\PromotionList;

class OrderController extends Controller
{

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
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function clearBills($business, $order, Request $request)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'sometimes|required|in:online,wallet,bkash,cbl',
            ]);
            if ($request->payment_method == 'bkash' && $this->hasPreviousBkashTransaction($request->job->partner_order_id)) {
                return api_response($request, null, 500, ['message' => "Can't send multiple requests within 1 minute."]);
            }
            $order_adapter = new OrderAdapter($request->job->partnerOrder);
            $payment = (new ShebaPayment($request->has('payment_method') ? $request->payment_method : 'online'))->init($order_adapter->getPayable());
            return api_response($request, $payment, 200, ['payment' => $payment->getFormattedPayment()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
                'code' => 'required|string',
            ]);
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            $geo = json_decode($business->geo_informations);
            if (!$customer) $customer = $this->createCustomerFromMember($member);
            $request->merge(['lat' => (double)$geo->lat, 'lng' => (double)$geo->lng]);
            $partnerListRequest->setRequest($request)->prepareObject();
            $hyper_local = HyperLocal::insidePolygon((double)$geo->lat, (double)$geo->lng)->with('location')->first();
            $location = $hyper_local ? $hyper_local->location->id : null;
            $order_amount = $promotionCalculation->calculateOrderAmount($partnerListRequest, $request->partner);
            if (!$order_amount) return api_response($request, null, 403);
            $result = voucher($request->code)
                ->check($partnerListRequest->selectedCategory->id, $request->partner, $location, $customer, $order_amount, 'Business')
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
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
                'voucher' => 'string',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
            ], ['mobile' => 'Invalid mobile number!']);
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            if (!$customer) {
                $customer = $this->createCustomerFromMember($member);
                $member = Member::find($member->id);
                $address = $this->createAddress($member, $business);
            } else {
                $geo = json_decode($business->geo_informations);
                $coords = new Coords($geo->lat, $geo->lng);
                $address = (new AddressValidator())->isAddressLocationExists($customer->delivery_addresses, $coords);
                if (!$address) $address = $this->createAddress($member, $business);
            }
            $order = new Checkout($customer);
            $request->merge(['customer' => $customer,
                'address_id' => $address->id,
                'name' => $business->name, 'payment_method' => 'cod', 'mobile' => $member->profile->mobile,
                'business_id' => $business->id, 'sales_channel' => 'Business', 'voucher' => $request->voucher]);
            $order = $order->placeOrder($request);
            if ($order) {
                return api_response($request, $order, 200, ['job_id' => $order->jobs->first()->id, 'order_id' => $order->partnerOrders->first()->id,
                    'order_code' => $order->code()]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function createCustomerFromMember(Member $member)
    {
        $customer = new Customer();
        $customer->profile_id = $member->profile->id;
        $customer->remember_token = str_random(255);
        $customer->save();
        return $customer;

    }

    private function createAddress(Member $member, Business $business)
    {
        $address = new CustomerDeliveryAddress();
        $address->address = $business->address;
        $address->name = $business->name;
        $geo = json_decode($business->geo_informations);
        $address->geo_informations = $business->geo_informations;
        $address->location_id = HyperLocal::insidePolygon($geo->lat, $geo->lng)->with('location')->first()->location->id;
        $address->customer_id = $member->profile->customer->id;
        $address->mobile = $member->profile->mobile;
        $address->save();
        return $address;
    }

}