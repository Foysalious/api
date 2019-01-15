<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Profile;
use App\Sheba\Address\AddressValidator;
use App\Sheba\Checkout\Checkout;
use App\Transformers\CustomSerializer;
use App\Transformers\JobTransformer;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Voucher\Creator\Referral;

class OrderController extends Controller
{
    use Helpers;

    public function show($order, Request $request)
    {
        try {
            $request->merge(['mobile' => formatMobile($request->mobile)]);
            $job = Job::find((int)$order);
            $job->load('partnerOrder.order.customer');
            $customer = $job->partnerOrder->order->customer;
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v2/customers/' . $customer->id . '/jobs/' . $order . '?remember_token=' . $customer->remember_token);
            $data = json_decode($response->getBody());
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Item($data->job, new JobTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            return response()->json(['data' => null]);
        }
    }

    public function placeOrder(Request $request)
    {
        try {
            $request->merge(['mobile' => trim(formatMobile($request->mobile))]);
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'services' => 'required|string',
                'partner' => 'required',
                'mobile' => 'required|string|mobile:bd',
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'address' => 'required',
                'is_on_premise' => 'sometimes|numeric',
                'name' => 'required'
            ], ['mobile' => 'Invalid mobile number!']);
            $hyper_local = HyperLocal::insidePolygon($request->lat, $request->lng)->first();
            $request->merge(['payment_method' => 'cod']);
            $profile = Profile::where('mobile', $request->mobile)->first();
            if ($profile) {
                $customer = $profile->customer;
                if (!$customer) $customer = $this->createCustomer($profile);
            } else {
                $profile = $this->createProfile();
                $customer = $this->createCustomer($profile);
            }
            $order = new Checkout($customer);
            $address = (new AddressValidator())->isAddressNameExists($customer->delivery_addresses, $request->address);
            if (!$address) {
                $address = new CustomerDeliveryAddress();
                $address->address = $request->address;
                $address->name = $request->name;
                $address->geo_informations = json_encode(['lat' => $request->lat, 'lng' => $request->lng]);
                $address->location_id = $hyper_local->location->id;
                $address->customer_id = $customer->id;
                $address->mobile = formatMobile($request->mobile);
                $address->save();
            }
            $request->merge(['address_id' => $address->id]);
            $request->merge(['sales_channel' => 'store']);
            $order = $order->placeOrder($request);
            if ($order) return response()->json(['data' => ['id' => $order->partnerOrders[0]->jobs[0]->id, 'message' => 'SUCCESSFUL']]);
            else return response()->json(['data' => null]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }

    private function createProfile()
    {
        $profile = new Profile();
        $profile->mobile = \request()->mobile;
        $profile->name = \request()->name;
        $profile->remember_token = str_random(255);
        $profile->save();
        return $profile;
    }

    private function createCustomer(Profile $profile)
    {
        $customer = new Customer();
        $customer->profile_id = $profile->id;
        $customer->remember_token = str_random(255);
        $customer->save();
        new Referral($customer);
        return $customer;
    }
}