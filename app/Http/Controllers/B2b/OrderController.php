<?php namespace App\Http\Controllers\B2b;


use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Member;
use App\Sheba\Address\AddressValidator;
use App\Sheba\Checkout\Checkout;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\Coords;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:ongoing,history'
            ]);
            $member = Member::find(1);
            $customer = $member->profile->customer;
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

    public function placeOrder(Request $request)
    {
        try {
            $request->merge(['mobile' => trim(formatMobile($request->mobile))]);
            $this->validate($request, [
                'services' => 'required|string',
                'partner' => 'required',
                'mobile' => 'required|string|mobile:bd',
                'name' => 'required',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'is_on_premise' => 'sometimes|numeric',
            ], ['mobile' => 'Invalid mobile number!']);
            $business = $request->business;
            $member = $request->member;
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
            $request->merge(['customer' => $customer, 'address_id' => $address->id, 'payment_method' => 'cod', 'business_id' => $business->id, 'sales_channel' => 'Business']);
            $request->merge(['address_id' => $address->id]);
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