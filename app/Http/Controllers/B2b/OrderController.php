<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Member;
use App\Models\PartnerOrder;
use App\Models\Payment;
use App\Sheba\Address\AddressValidator;
use App\Sheba\Checkout\Checkout;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\Coords;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:ongoing,history'
            ]);
            $customer = $request->manager_member->profile->customer;
            if ($customer) {
                $url = config('sheba.api_url') . "/v2/customers/$customer->id/orders?remember_token=$customer->remember_token&for=business&filter=$request->filter";
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
            dd($e);
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

    public function applyPromo(Request $request)
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
            $client = new Client();
            $geo = json_decode($business->geo_informations);
            $result = $client->request('POST', config('sheba.api_url') . '/v2/customers/' . $customer->id . '/promotions',
                [
                    'form_params' => [
                        'services' => $request->services,
                        'remember_token' => $customer->remember_token,
                        'partner' => $request->partner,
                        'date' => $request->date,
                        'time' => $request->time,
                        'lat' => $geo->lat,
                        'lng' => $geo->lng,
                        'code' => $request->code,
                        'sales_channel' => 'Business',
                    ]
                ]);
            $result = json_decode($result->getBody());
            return api_response($request, $result, $result->code, ['message' => $result->message]);
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
                'mobile' => 'required|string|mobile:bd',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'is_on_premise' => 'sometimes|numeric',
            ], ['mobile' => 'Invalid mobile number!']);
            $business = $request->business;
            $member = $request->manager_member;
            $customer = $member->profile->customer;
            if (!$customer) {
                $customer = $this->createCustomerFromMember($member);
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
                'name' => $business->name, 'payment_method' => 'cod',
                'business_id' => $business->id, 'sales_channel' => 'Business']);
            $order = $order->placeOrder($request);
            return api_response($request, $order, 200, ['job_id' => $order->jobs->first()->id, 'order_code' => $order->code()]);
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

    }

    private function createAddress(Member $member, Business $business)
    {
        $address = new CustomerDeliveryAddress();
        $address->address = $member->business->address;
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